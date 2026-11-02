<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\BuyTrade;
use App\Models\SellTrade;
use App\Models\CryptoRate;
use App\Models\Withdrawal;
use App\Models\Conversion;
use App\Services\CryptomusService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;

class CryptoController extends Controller
{
    public function dashboard()
    {
        $userId = Auth::id();
        Log::info('Signed-in User ID: ', ['user_id' => $userId]);

        // Fetch crypto rates
        $rates = CryptoRate::all(['coin', 'buy_rate', 'sell_rate'])->keyBy('coin')->toArray();
        Log::info('Rates fetched for dashboard: ', ['rates' => $rates]);

        // Fetch recent trades and withdrawals
        $buyTrades = BuyTrade::where('user_id', $userId)->latest()->limit(5)->get();
        $sellTrades = SellTrade::where('user_id', $userId)->latest()->limit(5)->get();
        $withdrawals = Withdrawal::where('user_id', $userId)->latest()->limit(5)->get();

        Log::info('Dashboard Data Fetched: ', [
            'buy_trades_count' => $buyTrades->count(),
            'sell_trades_count' => $sellTrades->count(),
            'withdrawals_count' => $withdrawals->count(),
            'buy_trades' => $buyTrades->toArray(),
            'sell_trades' => $sellTrades->toArray(),
            'withdrawals' => $withdrawals->toArray(),
        ]);

        // Transform and combine transactions
        $buyTransactions = $buyTrades->map(function ($trade) {
            return [
                'created_at' => $trade->created_at,
                'type' => 'buy',
                'coin' => $trade->coin,
                'amount_usd' => $trade->usd_amount,
                'amount_ngn' => $trade->naira_amount,
                'status' => strtolower($trade->status),
                'bank_account' => null,
            ];
        });

        $sellTransactions = $sellTrades->map(function ($trade) {
            return [
                'created_at' => $trade->created_at,
                'type' => 'sell',
                'coin' => $trade->coin,
                'amount_usd' => $trade->usd_amount ?? 0.00,
                'amount_ngn' => $trade->naira_amount ?? 0.00,
                'status' => strtolower($trade->status ?? 'N/A'),
                'bank_account' => $trade->bank_name ? "{$trade->bank_name} ({$trade->account_number})" : null,
            ];
        });

        $withdrawalTransactions = $withdrawals->map(function ($withdrawal) {
            $bankAccount = $withdrawal->bank_account ?? 'N/A';
            if (is_string($withdrawal->bank_account)) {
                $bankDetails = json_decode($withdrawal->bank_account, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($bankDetails['bank_name'])) {
                    $bankAccount = "{$bankDetails['bank_name']} ({$bankDetails['account_number']})";
                }
            } elseif (is_array($withdrawal->bank_account) && isset($withdrawal->bank_account['bank_name'])) {
                $bankAccount = "{$withdrawal->bank_account['bank_name']} ({$withdrawal->bank_account['account_number']})";
            }
            return [
                'created_at' => $withdrawal->created_at,
                'type' => 'withdrawal',
                'coin' => null,
                'amount_usd' => null,
                'amount_ngn' => $withdrawal->amount,
                'status' => strtolower($withdrawal->status),
                'bank_account' => $bankAccount,
            ];
        });

        // Combine transactions using concat instead of merge
        $transactions = $buyTransactions->concat($sellTransactions)->concat($withdrawalTransactions)
            ->sortByDesc('created_at')->values()->take(5);

        Log::info('Combined Transactions for Dashboard: ', ['count' => $transactions->count(), 'transactions' => $transactions->toArray()]);

        // USDT sell rate for NGN→USD balance conversion
        $usdtRate = CryptoRate::where('coin', 'USDT')->value('sell_rate') ?? 1;

        // Referral count for dashboard stat tile
        $referralCount = Auth::user()->referralsMade()->count();

        // Latest published blog posts for dashboard carousel
        $blogPosts = \App\Models\BlogPost::published()->limit(8)->get();

        return view('dashboard', compact('transactions', 'rates', 'usdtRate', 'referralCount', 'blogPosts'));
    }

    public function buy()
    {
        $rates = CryptoRate::pluck('buy_rate', 'coin')->toArray();
        return view('buy', compact('rates'));
    }

    public function sell()
    {
        $rates = CryptoRate::whereIn('coin', ['BTC', 'ETH', 'USDT'])
            ->pluck('sell_rate', 'coin')
            ->toArray();
        Log::info('Sell rates fetched: ', ['rates' => $rates]);
        return view('sell', compact('rates'));
    }

    public function convert()
    {
        // Check if convert feature is enabled
        $convertEnabled = \App\Models\AdminSetting::getSetting('cryptomus_convert_enabled', '1');
        if ($convertEnabled !== '1') {
            abort(404, 'Convert feature is currently disabled.');
        }

        $rateService = new \App\Services\ExchangeRateService();
        $usdRates    = $rateService->getUsdRates();

        $rateSource  = $usdRates['_source'] ?? 'fallback';
        $ratesUpdated = $usdRates['_updated_at'] ?? null;
        unset($usdRates['_source'], $usdRates['_updated_at']);

        $coins = [];
        foreach (\App\Services\ExchangeRateService::SUPPORTED_COINS as $symbol => $meta) {
            $coins[$symbol] = [
                'name' => $meta['name'],
                'usd'  => $usdRates[$symbol] ?? $meta['fallback'],
            ];
        }

        $feePercent = (float) \App\Models\AdminSetting::getSetting('convert_fee_percent', '0.5');
        $convertMode = \App\Models\AdminSetting::getSetting('convert_mode', 'cryptomus'); // compare | cryptomus | manual
        if (!in_array($convertMode, ['compare', 'cryptomus', 'manual'], true)) {
            $convertMode = 'cryptomus';
        }
        $cryptomusReady = (new CryptomusService())->isEnabled();
        $convertInstructions = \App\Models\AdminSetting::getSetting('convert_instructions', '');

        $recentConversions = Conversion::where('user_id', Auth::id())
            ->latest()
            ->limit(5)
            ->get();

        return view('convert', compact('coins', 'feePercent', 'convertMode', 'cryptomusReady', 'convertInstructions', 'recentConversions', 'rateSource', 'ratesUpdated'));
    }

    /**
     * AJAX: live conversion quote.
     */
    public function convertQuote(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_coin'   => ['required', 'string', 'max:10'],
            'to_coin'     => ['required', 'string', 'max:10'],
            'from_amount' => ['required', 'numeric', 'min:0.00000001'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Invalid input', 'errors' => $validator->errors()], 422);
        }

        $fromCoin = strtoupper($request->from_coin);
        $toCoin   = strtoupper($request->to_coin);

        $rateService = new \App\Services\ExchangeRateService();

        if (!$rateService->isSupported($fromCoin) || !$rateService->isSupported($toCoin)) {
            return response()->json(['success' => false, 'message' => 'Unsupported coin selected'], 400);
        }

        if ($fromCoin === $toCoin) {
            return response()->json(['success' => false, 'message' => 'Choose two different coins'], 400);
        }

        try {
            $feePercent = (float) \App\Models\AdminSetting::getSetting('convert_fee_percent', '0.5');
            $quote = $rateService->quote($fromCoin, $toCoin, (float) $request->from_amount, $feePercent);

            return response()->json([
                'success' => true,
                'quote'   => [
                    'rate'           => round($quote['rate'], 8),
                    'to_amount'      => round($quote['to_amount'], 8),
                    'fee_amount'     => round($quote['fee_amount'], 8),
                    'fee_percent'    => $quote['fee_percent'],
                    'receive_amount' => round($quote['receive_amount'], 8),
                    'usd_value'      => round($quote['usd_value'], 2),
                    'source'         => $quote['source'],
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Unable to fetch a quote right now.'], 502);
        }
    }

    public function convertSubmit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_coin' => ['required', 'string', 'max:10'],
            'to_coin' => ['required', 'string', 'max:10'],
            'from_amount' => ['required', 'numeric', 'min:0.00000001'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Feature toggle
            if (\App\Models\AdminSetting::getSetting('cryptomus_convert_enabled', '1') !== '1') {
                return response()->json([
                    'success' => false,
                    'message' => 'The convert feature is currently disabled.'
                ], 403);
            }

            // Mode gate — manual mode uses its own endpoint, compare mode has no submission
            $convertMode = \App\Models\AdminSetting::getSetting('convert_mode', 'cryptomus');
            if ($convertMode === 'manual') {
                return response()->json([
                    'success' => false,
                    'message' => 'Manual conversion mode is active. Please use the manual conversion form.'
                ], 400);
            }
            if ($convertMode !== 'cryptomus') {
                return response()->json([
                    'success' => false,
                    'message' => 'Conversions are currently in compare-only mode.'
                ], 400);
            }

            $cryptomusService = new CryptomusService();

            // Check if Cryptomus is enabled
            if (!$cryptomusService->isEnabled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Crypto conversion service is not configured. Please contact support.'
                ], 503);
            }

            $user = Auth::user();
            $fromCoin = strtoupper($request->from_coin);
            $toCoin = strtoupper($request->to_coin);
            $fromAmount = (float) $request->from_amount;

            $rateService = new \App\Services\ExchangeRateService();

            if (!$rateService->isSupported($fromCoin) || !$rateService->isSupported($toCoin) || $fromCoin === $toCoin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid cryptocurrency pair selected'
                ], 400);
            }

            // Server-side quote — never trust client-supplied amounts
            $feePercent = (float) \App\Models\AdminSetting::getSetting('convert_fee_percent', '0.5');
            $quote = $rateService->quote($fromCoin, $toCoin, $fromAmount, $feePercent);

            $feeAmount = $quote['fee_amount'];
            $finalToAmount = $quote['receive_amount'];
            $fromAmountInUsd = $quote['usd_value'];

            if ($fromAmountInUsd < 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Minimum conversion is $1.00 worth of crypto.'
                ], 400);
            }

            // Create conversion record
            $conversion = Conversion::create([
                'user_id' => $user->id,
                'from_coin' => $fromCoin,
                'to_coin' => $toCoin,
                'from_amount' => $fromAmount,
                'to_amount' => $finalToAmount,
                'fee_amount' => $feeAmount,
                'rate_used' => $quote['rate'],
                'status' => 'pending',
                'mode' => 'cryptomus',
            ]);

            // Create Cryptomus payment: user pays the USD-equivalent in the source coin

            $paymentData = [
                'amount' => number_format($fromAmountInUsd, 2, '.', ''),
                'currency' => 'USD', // Pay in USD equivalent
                'crypto_currency' => $fromCoin, // User pays in the source coin
                'order_id' => 'conv_' . $conversion->id,
                'url_callback' => route('cryptomus.webhook'),
                'url_return' => route('convert.success', $conversion->id),
                'lifetime' => 3600, // 1 hour
                'additional_data' => json_encode([
                    'conversion_id' => $conversion->id,
                    'user_id' => $user->id,
                    'from_coin' => $fromCoin,
                    'to_coin' => $toCoin,
                    'expected_to_amount' => $finalToAmount
                ]),
            ];

            try {
                $payment = $cryptomusService->createPayment($paymentData);
            } catch (\Exception $e) {
                // Don't leave a dangling pending record when the payment API fails
                $conversion->update([
                    'status' => 'failed',
                    'failure_reason' => 'Payment gateway error: ' . $e->getMessage(),
                ]);
                throw $e;
            }

            // Cryptomus wraps the payload in a "result" key
            $paymentResult = $payment['result'] ?? [];

            // Update conversion with Cryptomus order ID
            $conversion->update([
                'cryptomus_order_id' => $paymentResult['uuid'] ?? null,
                'cryptomus_response' => $payment,
                'status' => 'processing',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Conversion initiated successfully',
                'conversion_id' => $conversion->id,
                'payment_url' => $paymentResult['url'] ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error('Conversion submission failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process conversion. Please try again.'
            ], 500);
        }
    }

    /**
     * Manual conversion: user submits wallet address + amount, admin processes it.
     */
    public function convertManualSubmit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_coin'      => ['required', 'string', 'max:10'],
            'to_coin'        => ['required', 'string', 'max:10'],
            'from_amount'    => ['required', 'numeric', 'min:0.00000001'],
            'wallet_address' => ['required', 'string', 'min:10', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            if (\App\Models\AdminSetting::getSetting('cryptomus_convert_enabled', '1') !== '1') {
                return response()->json(['success' => false, 'message' => 'The convert feature is currently disabled.'], 403);
            }

            if (\App\Models\AdminSetting::getSetting('convert_mode', 'cryptomus') !== 'manual') {
                return response()->json(['success' => false, 'message' => 'Manual conversion mode is not active.'], 400);
            }

            $user = Auth::user();
            $fromCoin = strtoupper($request->from_coin);
            $toCoin   = strtoupper($request->to_coin);
            $fromAmount = (float) $request->from_amount;

            $rateService = new \App\Services\ExchangeRateService();

            if (!$rateService->isSupported($fromCoin) || !$rateService->isSupported($toCoin) || $fromCoin === $toCoin) {
                return response()->json(['success' => false, 'message' => 'Invalid cryptocurrency pair selected'], 400);
            }

            $feePercent = (float) \App\Models\AdminSetting::getSetting('convert_fee_percent', '0.5');
            $quote = $rateService->quote($fromCoin, $toCoin, $fromAmount, $feePercent);

            if ($quote['usd_value'] < 1) {
                return response()->json(['success' => false, 'message' => 'Minimum conversion is $1.00 worth of crypto.'], 400);
            }

            $conversion = Conversion::create([
                'user_id'        => $user->id,
                'from_coin'      => $fromCoin,
                'to_coin'        => $toCoin,
                'from_amount'    => $fromAmount,
                'to_amount'      => $quote['receive_amount'],
                'fee_amount'     => $quote['fee_amount'],
                'rate_used'      => $quote['rate'],
                'status'         => 'pending',
                'mode'           => 'manual',
                'wallet_address' => trim($request->wallet_address),
            ]);

            Log::info('Manual conversion requested', [
                'conversion_id' => $conversion->id,
                'user_id'       => $user->id,
                'pair'          => "{$fromCoin}->{$toCoin}",
                'amount'        => $fromAmount,
            ]);

            return response()->json([
                'success'       => true,
                'message'       => 'Conversion request submitted. Our team will process it shortly.',
                'conversion_id' => $conversion->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Manual conversion submission failed', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);

            return response()->json(['success' => false, 'message' => 'Failed to submit conversion. Please try again.'], 500);
        }
    }

    public function convertSuccess($id)
    {
        $conversion = Conversion::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('convert.success', compact('conversion'));
    }

    public function convertFailed($id)
    {
        $conversion = Conversion::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('convert.failed', compact('conversion'));
    }

    public function sellPostStep1(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'coin' => ['required', 'in:BTC,ETH,USDT'],
            'input_type' => ['required', 'in:usd,naira'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'usd_amount' => ['required', 'numeric', 'min:0.01'],
            'naira_amount' => ['required', 'numeric', 'min:0.01'],
            'proof' => ['required', 'file', 'mimes:png,jpg,jpeg', 'max:2048'],
            'payment_method' => ['required', 'in:external_bank,other'],
            'wallet_address' => ['required', 'string', 'max:255'],
            'bank_name' => ['required_if:payment_method,external_bank', 'string', 'max:255'],
            'account_number' => ['required_if:payment_method,external_bank', 'string', 'max:20'],
            'account_name' => ['required_if:payment_method,external_bank', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            Log::warning('Sell trade validation failed: ', $validator->errors()->toArray());
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $proofPath = $request->file('proof')->store('payment_proofs', 'public');
            $transactionRef = 'SELL-' . Str::random(8);

            $sellTrade = SellTrade::create([
                'user_id' => Auth::id(),
                'coin' => $request->coin,
                'usd_amount' => $request->usd_amount,
                'naira_amount' => $request->naira_amount,
                'proof' => $proofPath,
                'payment_method' => $request->payment_method,
                'status' => 'Pending',
                'name' => Auth::user()->name,
                'transaction_ref' => $transactionRef,
                'wallet_address' => $request->wallet_address,
                'bank_name' => $request->bank_name,
                'account_number' => $request->account_number,
                'account_name' => $request->account_name,
            ]);

            Log::info('Sell trade created: ', $sellTrade->toArray());
            return redirect()->route('dashboard')->with('success', 'Sell trade submitted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to save trade: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to save trade. Please try again.');
        }
    }

    public function withdraw()
    {
        $balance = Auth::user()->balance;
        return view('withdraw', compact('balance'));
    }

    public function processWithdraw(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => ['required', 'numeric', 'min:1', 'max:' . Auth::user()->balance],
            'bank_account' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $withdrawal = Withdrawal::create([
                'user_id' => Auth::id(),
                'amount' => $request->amount,
                'bank_account' => $request->bank_account,
                'status' => 'pending',
                'currency' => 'NGN',
                'reference' => Str::random(16),
            ]);

            Log::info('Withdrawal created: ', $withdrawal->toArray());
            return redirect()->route('dashboard')->with('success', 'Withdrawal request submitted successfully.');
        } catch (\Exception $e) {
            Log::error('Error processing withdrawal: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to process withdrawal. Please try again.');
        }
    }

    public function transactions(Request $request)
    {
        $userId = Auth::id();
        $type = $request->query('type', 'all');
        $search = $request->query('search', '');
        $status = $request->query('status', '');
        $date = $request->query('date', '');
        $perPage = 10;

        Log::info('Transactions Request: ', [
            'user_id' => $userId,
            'is_ajax' => $request->ajax(),
            'request_headers' => $request->headers->all(),
            'type' => $type,
            'search' => $search,
            'status' => $status,
            'date' => $date,
        ]);

        // Initialize queries
        $buyTradesQuery = BuyTrade::where('user_id', $userId);
        $sellTradesQuery = SellTrade::where('user_id', $userId);
        $withdrawalsQuery = Withdrawal::where('user_id', $userId);

        // Apply search filter
        if ($search) {
            $buyTradesQuery->where('coin', 'like', '%' . $search . '%');
            $sellTradesQuery->where('coin', 'like', '%' . $search . '%');
            $withdrawalsQuery->where('bank_account', 'like', '%' . $search . '%');
        }

        // Apply status filter (case-insensitive)
        if ($status) {
            $buyTradesQuery->whereRaw('LOWER(status) = ?', [strtolower($status)]);
            $sellTradesQuery->whereRaw('LOWER(status) = ?', [strtolower($status)]);
            $withdrawalsQuery->whereRaw('LOWER(status) = ?', [strtolower($status)]);
        }

        // Apply date filter
        if ($date) {
            $buyTradesQuery->whereDate('created_at', $date);
            $sellTradesQuery->whereDate('created_at', $date);
            $withdrawalsQuery->whereDate('created_at', $date);
        }

        // Apply type filter
        $buyTrades = ($type === 'all' || $type === 'buy') ? $buyTradesQuery->latest()->get() : collect([]);
        $sellTrades = ($type === 'all' || $type === 'sell') ? $sellTradesQuery->latest()->get() : collect([]);
        $withdrawals = ($type === 'all' || $type === 'withdrawal') ? $withdrawalsQuery->latest()->get() : collect([]);

        Log::info('Fetched Data: ', [
            'buy_trades_count' => $buyTrades->count(),
            'sell_trades_count' => $sellTrades->count(),
            'withdrawals_count' => $withdrawals->count(),
            'buy_trades' => $buyTrades->toArray(),
            'sell_trades' => $sellTrades->toArray(),
            'withdrawals' => $withdrawals->toArray(),
        ]);

        // Combine transactions
        $buyTransactions = $buyTrades->map(function ($trade) {
            return [
                'created_at' => $trade->created_at,
                'type' => 'buy',
                'coin' => $trade->coin,
                'amount_usd' => $trade->usd_amount,
                'amount_ngn' => $trade->naira_amount,
                'status' => strtolower($trade->status),
                'bank_account' => null,
            ];
        });

        $sellTransactions = $sellTrades->map(function ($trade) {
            return [
                'created_at' => $trade->created_at,
                'type' => 'sell',
                'coin' => $trade->coin,
                'amount_usd' => $trade->usd_amount ?? 0.00,
                'amount_ngn' => $trade->naira_amount ?? 0.00,
                'status' => strtolower($trade->status ?? 'N/A'),
                'bank_account' => $trade->bank_name ? "{$trade->bank_name} ({$trade->account_number})" : null,
            ];
        });

        $withdrawalTransactions = $withdrawals->map(function ($withdrawal) {
            $bankAccount = $withdrawal->bank_account ?? 'N/A';
            if (is_string($withdrawal->bank_account)) {
                $bankDetails = json_decode($withdrawal->bank_account, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($bankDetails['bank_name'])) {
                    $bankAccount = "{$bankDetails['bank_name']} ({$bankDetails['account_number']})";
                }
            } elseif (is_array($withdrawal->bank_account) && isset($withdrawal->bank_account['bank_name'])) {
                $bankAccount = "{$withdrawal->bank_account['bank_name']} ({$withdrawal->bank_account['account_number']})";
            }
            return [
                'created_at' => $withdrawal->created_at,
                'type' => 'withdrawal',
                'coin' => null,
                'amount_usd' => null,
                'amount_ngn' => $withdrawal->amount,
                'status' => strtolower($withdrawal->status),
                'bank_account' => $bankAccount,
            ];
        });

        // Combine transactions using concat
        $transactions = $buyTransactions->concat($sellTransactions)->concat($withdrawalTransactions)
            ->sortByDesc('created_at')->values();

        Log::info('Combined Transactions: ', [
            'count' => $transactions->count(),
            'transactions' => $transactions->toArray(),
            'transaction_class' => get_class($transactions),
        ]);

        // Handle AJAX request
        if ($request->ajax()) {
            $transactions = $transactions->take(5);
            Log::info('AJAX Response: ', [
                'count' => $transactions->count(),
                'transactions' => $transactions->toArray(),
                'transaction_class' => get_class($transactions),
            ]);
            return response()->json($transactions);
        }

        // Always paginate for non-AJAX
        $currentPage = max(1, (int) $request->query('page', 1));
        $total = $transactions->count();
        $paginatedItems = $total > 0 ? $transactions->forPage($currentPage, $perPage) : collect([]);
        $transactions = new LengthAwarePaginator(
            $paginatedItems,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        Log::info('Paginated Transactions: ', [
            'page' => $currentPage,
            'total' => $total,
            'per_page' => $perPage,
            'items_count' => $paginatedItems->count(),
            'items' => $paginatedItems->toArray(),
            'paginator_class' => get_class($transactions),
        ]);

        return view('transactions.index', compact('transactions'));
    }

    public function kyc()
    {
        return view('kyc');
    }
}