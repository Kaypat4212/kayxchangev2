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
        $rateCoins = CryptoRate::query()->pluck('coin')->map(fn ($coin) => strtoupper((string) $coin))->merge(['BTC', 'ETH', 'USDT', 'SOL', 'BNB', 'USDC', 'LTC', 'ADA', 'DOGE'])->unique()->values()->all();
        $rates = app(\App\Services\CryptoTradeRateService::class)->rates($rateCoins);
        Log::info('Rates fetched for dashboard: ', ['rates' => $rates]);
        $usdtRate = app(\App\Services\CryptoTradeRateService::class)->rate('USDT', 'sell') ?? 0;
        $walletMarket = app(\App\Services\ExchangeRateService::class)->getUsdRates();

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

        $withdrawalTransactions = $withdrawals->map(function ($withdrawal) use ($usdtRate) {
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
                'amount_usd' => $usdtRate > 0 ? ((float)$withdrawal->amount + (float)($withdrawal->fee_amount ?? 0)) / $usdtRate : null,
                'amount_ngn' => (float)$withdrawal->amount + (float)($withdrawal->fee_amount ?? 0),
                'status' => strtolower($withdrawal->status),
                'bank_account' => $bankAccount,
            ];
        });

        $depositTransactions = \App\Models\Deposit::where('user_id', $userId)->latest()->limit(5)->get()->map(function ($deposit) use ($usdtRate) {
            $ngn = max(0, (float) $deposit->amount - (float) ($deposit->fee_amount ?? 0));
            return ['created_at'=>$deposit->created_at, 'type'=>'deposit', 'coin'=>'NGN wallet', 'amount_usd'=>$usdtRate > 0 ? $ngn / $usdtRate : null, 'amount_ngn'=>$ngn, 'status'=>strtolower((string)$deposit->status), 'bank_account'=>null];
        });
        $cryptoDepositTransactions = \App\Models\CryptoDeposit::where('user_id', $userId)->latest()->limit(5)->get()->map(function ($deposit) use ($walletMarket, $usdtRate) {
            $coin = strtoupper((string) $deposit->currency);
            $crypto = (float) ($deposit->received_amount ?: $deposit->expected_amount);
            $usd = $crypto * (float) ($walletMarket[$coin] ?? 0);
            $ngn = (float) ($deposit->ngn_credited_amount ?? 0);
            return ['created_at'=>$deposit->created_at, 'type'=>'deposit', 'coin'=>$coin . ' crypto wallet', 'amount_usd'=>$usd > 0 ? $usd : null, 'amount_ngn'=>$ngn > 0 ? $ngn : ($usdtRate > 0 && $usd > 0 ? $usd * $usdtRate : null), 'status'=>strtolower((string)$deposit->status), 'bank_account'=>null];
        });
        $cryptoWithdrawalTransactions = \App\Models\CryptoWithdrawal::where('user_id', $userId)->with('wallet')->latest()->limit(5)->get()->map(function ($withdrawal) use ($walletMarket, $usdtRate) {
            $coin = strtoupper((string) ($withdrawal->wallet?->currency ?? 'CRYPTO'));
            $usd = (float) ($withdrawal->usd_value_at_request ?? 0);
            if ($usd <= 0) $usd = (float) $withdrawal->amount * (float) ($walletMarket[$coin] ?? 0);
            return ['created_at'=>$withdrawal->created_at, 'type'=>'withdrawal', 'coin'=>$coin . ' crypto wallet', 'amount_usd'=>$usd > 0 ? $usd : null, 'amount_ngn'=>$usdtRate > 0 && $usd > 0 ? $usd * $usdtRate : null, 'status'=>strtolower((string)$withdrawal->status), 'bank_account'=>$withdrawal->network ?: 'Crypto wallet'];
        });

        // Combine transactions using concat instead of merge
        $transactions = $buyTransactions->concat($sellTransactions)->concat($withdrawalTransactions)->concat($depositTransactions)->concat($cryptoDepositTransactions)->concat($cryptoWithdrawalTransactions)
            ->sortByDesc('created_at')->values()->take(5);

        // Keep the dashboard focused on the five most recent account events; the activity page shows the paginated feed.
        $recentActivities = app(\App\Services\UserActivityFeedService::class)->forUser(Auth::user(), 20, $walletMarket, (float)$usdtRate)->take(5)->values();
        $totalTradeCount = (int) BuyTrade::where('user_id', $userId)->count() + (int) SellTrade::where('user_id', $userId)->count();
        $pendingCount = 0;
        foreach ([BuyTrade::class, SellTrade::class, Withdrawal::class, \App\Models\Deposit::class, \App\Models\CryptoDeposit::class, \App\Models\CryptoWithdrawal::class, Conversion::class, \App\Models\BillPayment::class, \App\Models\GiftCardOrder::class, \App\Models\GiftCardSaleOrder::class] as $pendingModel) {
            try { $pendingCount += $pendingModel::where('user_id', $userId)->whereIn('status', ['pending', 'processing', 'waiting', 'confirming', 'partially_paid'])->count(); } catch (\Throwable $exception) {}
        }

        Log::info('Combined Transactions for Dashboard: ', ['count' => $transactions->count(), 'transactions' => $transactions->toArray()]);

        // Referral count for dashboard stat tile
        $referralCount = Auth::user()->referralsMade()->count();

        // Latest published blog posts for dashboard carousel
        $blogPosts = \App\Models\BlogPost::published()->limit(8)->get();

        $billService = app(\App\Services\BillPaymentProviderService::class);
        $billPaymentsEnabled = $billService->billPaymentsEnabled() && $billService->providers() !== [];
        $utilityPaymentsEnabled = $billPaymentsEnabled && $billService->utilityPaymentsEnabled();
        $currentUser = Auth::user();
        app(\App\Services\CryptoWalletService::class)->getUserWallets($currentUser);
        $cryptoWallets = $currentUser->wallets()->where('is_active', true)->whereNotIn('currency', ['NGN', 'USD', 'EUR'])->orderBy('currency')->get();
        $cryptoPortfolioUsd = 0.0;
        $cryptoPortfolioComplete = true;
        foreach ($cryptoWallets as $cryptoWallet) {
            $asset = strtoupper((string) $cryptoWallet->currency);
            if (is_numeric($walletMarket[$asset] ?? null)) $cryptoPortfolioUsd += (float) $cryptoWallet->balance * (float) $walletMarket[$asset];
            elseif ((float) $cryptoWallet->balance > 0) $cryptoPortfolioComplete = false;
        }
        if (!$cryptoPortfolioComplete) $cryptoPortfolioUsd = null;
        $cryptoPortfolioStale = (bool) ($walletMarket['_stale'] ?? false);
        $dashboardLayoutService = app(\App\Services\DashboardStackService::class);
        $dashboardLayout = $dashboardLayoutService->forUser($currentUser);
        $dashboardDefaults = $dashboardLayoutService->adminDefaults();
        return view('dashboard', compact('transactions', 'rates', 'usdtRate', 'referralCount', 'blogPosts', 'billPaymentsEnabled', 'utilityPaymentsEnabled', 'cryptoPortfolioUsd', 'cryptoPortfolioStale', 'cryptoWallets', 'recentActivities', 'totalTradeCount', 'pendingCount', 'dashboardLayout', 'dashboardDefaults'));
    }

    public function buy()
    {
        $resolved = app(\App\Services\CryptoTradeRateService::class)->rates(['BTC', 'ETH', 'USDT']);
        $rates = array_map(fn ($rate) => $rate['buy_rate'], $resolved);
        return view('buy', compact('rates'));
    }

    public function sell()
    {
        $resolved = app(\App\Services\CryptoTradeRateService::class)->rates(['BTC', 'ETH', 'USDT']);
        $rates = array_map(fn ($rate) => $rate['sell_rate'], $resolved);
        Log::info('Sell rates fetched: ', ['rates' => $rates]);
        return view('sell', compact('rates'));
    }

    public function convert()
    {
        // Check if convert feature is enabled
        $convertEnabled = \App\Models\AdminSetting::getSetting('cryptomus_convert_enabled', '1');
        if ($convertEnabled !== '1') {
            abort(404, 'Swap feature is currently disabled.');
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

        $platformFeePercent = max(0, min(19.5, (float) \App\Models\AdminSetting::getSetting('convert_fee_percent', '0.5')));
        $convertMode = \App\Models\AdminSetting::getSetting('convert_mode', 'cryptomus'); // compare | cryptomus | manual
        if (!in_array($convertMode, ['compare', 'cryptomus', 'nowpayments', 'manual'], true)) {
            $convertMode = 'cryptomus';
        }
        if ($convertMode === 'nowpayments') {
            $coins = array_intersect_key($coins, array_flip(['BTC', 'ETH', 'USDT', 'USDC', 'BNB', 'SOL']));
        }
        $cryptomusReady = (new CryptomusService())->isEnabled();
        $nowPayments = app(\App\Services\NowPaymentsService::class);
        $swapReady = $nowPayments->isCustodyConversionReady();
        $feePercent = $convertMode === 'nowpayments'
            ? app(\App\Services\NowPaymentsConversionService::class)->totalFeePercent()
            : $platformFeePercent;
        $convertInstructions = \App\Models\AdminSetting::getSetting('convert_instructions', '');

        $recentConversions = Conversion::where('user_id', Auth::id())
            ->latest()
            ->limit(5)
            ->get();

        $walletBalances = Auth::user()->wallets()->where('is_active', true)->get()
            ->mapWithKeys(fn ($wallet) => [strtoupper($wallet->currency) => (float) $wallet->balance])->all();
        $swapRequestKey = (string) Str::uuid();

        return view('convert', compact('coins', 'walletBalances', 'swapRequestKey', 'feePercent', 'convertMode', 'cryptomusReady', 'swapReady', 'convertInstructions', 'recentConversions', 'rateSource', 'ratesUpdated'));
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
            $feePercent = max(0, min(19.5, (float) \App\Models\AdminSetting::getSetting('convert_fee_percent', '0.5')));
            if (\App\Models\AdminSetting::getSetting('convert_mode', 'cryptomus') === 'nowpayments') {
                $feePercent = app(\App\Services\NowPaymentsConversionService::class)->totalFeePercent();
            }
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
                if ($convertMode === 'nowpayments') {
                    return $this->submitNowPaymentsWalletSwap($request);
                }
                return response()->json([
                    'success' => false,
                    'message' => 'Swaps are currently disabled in compare-only mode.'
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
                'url_return' => route('swap.success', $conversion->id),
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

        if ($conversion->mode === 'nowpayments' && in_array($conversion->status, ['pending', 'processing'], true)) {
            try {
                $conversion = app(\App\Services\NowPaymentsConversionService::class)->refresh($conversion);
            } catch (\Throwable $e) {
                Log::warning('User conversion status refresh failed', ['conversion_id' => $conversion->id, 'error' => $e->getMessage()]);
            }
        }

        return view('convert.success', compact('conversion'));
    }

    public function convertStatus($id)
    {
        $conversion = Conversion::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        if ($conversion->mode === 'nowpayments' && !$conversion->provider_conversion_id && in_array($conversion->status, ['pending', 'processing'], true)) {
            return response()->json([
                'success' => true,
                'status' => $conversion->status,
                'syncable' => false,
                'to_amount' => (float) $conversion->to_amount,
                'fee_amount' => (float) $conversion->fee_amount,
                'to_coin' => $conversion->to_coin,
                'message' => 'The provider response is being reconciled. Your source balance remains reserved; support will verify the request before release.',
            ])->header('Cache-Control', 'private, no-store');
        }
        if ($conversion->mode === 'nowpayments' && in_array($conversion->status, ['pending', 'processing'], true)) {
            try {
                $conversion = app(\App\Services\NowPaymentsConversionService::class)->refresh($conversion);
            } catch (\Throwable $e) {
                Log::warning('NOWPayments conversion status poll failed', ['conversion_id' => $conversion->id, 'error' => $e->getMessage()]);
                return response()->json(['success' => false, 'message' => 'Status refresh is temporarily unavailable. Your reserved balance remains protected.'], 503);
            }
        }
        return response()->json([
            'success' => true,
            'status' => $conversion->status,
            'to_amount' => (float) $conversion->to_amount,
            'fee_amount' => (float) $conversion->fee_amount,
            'to_coin' => $conversion->to_coin,
            'provider_status' => $conversion->provider_status,
            'syncable' => $conversion->mode === 'nowpayments' && in_array($conversion->status, ['pending', 'processing'], true) && !empty($conversion->provider_conversion_id),
        ])->header('Cache-Control', 'private, no-store');
    }

    private function submitNowPaymentsWalletSwap(Request $request)
    {
        if (\App\Models\AdminSetting::getSetting('cryptomus_convert_enabled', '1') !== '1') {
            return response()->json(['success' => false, 'message' => 'Crypto swaps are currently disabled.'], 403);
        }

        $service = app(\App\Services\NowPaymentsConversionService::class);
        if (!app(\App\Services\NowPaymentsService::class)->isCustodyConversionReady()) {
            return response()->json(['success' => false, 'message' => 'Crypto swaps are not configured yet. Please try again later.'], 503);
        }

        $request->validate(['swap_request_key' => ['required', 'uuid']]);

        $fromCoin = strtoupper($request->input('from_coin'));
        $toCoin = strtoupper($request->input('to_coin'));
        $fromAmount = round((float) $request->input('from_amount'), 8);
        $rates = app(\App\Services\ExchangeRateService::class);
        if (!$rates->isSupported($fromCoin) || !$rates->isSupported($toCoin) || $fromCoin === $toCoin) {
            return response()->json(['success' => false, 'message' => 'Choose a supported, different cryptocurrency pair.'], 422);
        }

        try {
            $quote = $rates->quote($fromCoin, $toCoin, $fromAmount, $service->totalFeePercent());
            if ($quote['usd_value'] < 1) {
                return response()->json(['success' => false, 'message' => 'Minimum swap is $1.00 worth of crypto.'], 422);
            }
            $conversion = $service->submit(Auth::user(), $fromCoin, $toCoin, $fromAmount, $quote, (string) $request->input('swap_request_key'));
            return response()->json([
                'success' => true,
                'message' => $conversion->status === 'completed'
                    ? 'Swap completed and your destination wallet has been credited.'
                    : ($conversion->status === 'failed'
                        ? 'The provider rejected this swap and your source balance was restored.'
                        : 'Swap submitted. Your source amount is reserved while NOWPayments confirms the conversion.'),
                'conversion_id' => $conversion->id,
                'status' => $conversion->status,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            Log::error('NOWPayments wallet swap failed', ['user_id' => Auth::id(), 'from_coin' => $fromCoin, 'to_coin' => $toCoin, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'We could not safely complete this swap request. Please try again or contact support.'], 500);
        }
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
        $depositsQuery = \App\Models\Deposit::where('user_id', $userId);
        $cryptoDepositsQuery = \App\Models\CryptoDeposit::where('user_id', $userId);
        $cryptoWithdrawalsQuery = \App\Models\CryptoWithdrawal::where('user_id', $userId);

        // Apply search filter
        if ($search) {
            $buyTradesQuery->where('coin', 'like', '%' . $search . '%');
            $sellTradesQuery->where('coin', 'like', '%' . $search . '%');
            $withdrawalsQuery->where('bank_account', 'like', '%' . $search . '%');
            $depositsQuery->where(function ($query) use ($search) { $query->where('transaction_ref', 'like', '%' . $search . '%')->orWhere('payment_method', 'like', '%' . $search . '%'); });
            $cryptoDepositsQuery->where(function ($query) use ($search) { $query->where('currency', 'like', '%' . $search . '%')->orWhere('transaction_hash', 'like', '%' . $search . '%'); });
            $cryptoWithdrawalsQuery->where(function ($query) use ($search) { $query->where('network', 'like', '%' . $search . '%')->orWhere('transaction_hash', 'like', '%' . $search . '%'); });
        }

        // Apply status filter (case-insensitive)
        if ($status) {
            $buyTradesQuery->whereRaw('LOWER(status) = ?', [strtolower($status)]);
            $sellTradesQuery->whereRaw('LOWER(status) = ?', [strtolower($status)]);
            $withdrawalsQuery->whereRaw('LOWER(status) = ?', [strtolower($status)]);
            $depositsQuery->whereRaw('LOWER(status) = ?', [strtolower($status)]);
            $cryptoDepositsQuery->whereRaw('LOWER(status) = ?', [strtolower($status)]);
            $cryptoWithdrawalsQuery->whereRaw('LOWER(status) = ?', [strtolower($status)]);
        }

        // Apply date filter
        if ($date) {
            $buyTradesQuery->whereDate('created_at', $date);
            $sellTradesQuery->whereDate('created_at', $date);
            $withdrawalsQuery->whereDate('created_at', $date);
            $depositsQuery->whereDate('created_at', $date);
            $cryptoDepositsQuery->whereDate('created_at', $date);
            $cryptoWithdrawalsQuery->whereDate('created_at', $date);
        }

        // Apply type filter
        $buyTrades = ($type === 'all' || $type === 'buy') ? $buyTradesQuery->latest()->get() : collect([]);
        $sellTrades = ($type === 'all' || $type === 'sell') ? $sellTradesQuery->latest()->get() : collect([]);
        $withdrawals = ($type === 'all' || $type === 'withdrawal') ? $withdrawalsQuery->latest()->get() : collect([]);
        $deposits = ($type === 'all' || $type === 'deposit') ? $depositsQuery->latest()->get() : collect([]);
        $cryptoDeposits = ($type === 'all' || $type === 'deposit') ? $cryptoDepositsQuery->latest()->get() : collect([]);
        $cryptoWithdrawals = ($type === 'all' || $type === 'withdrawal') ? $cryptoWithdrawalsQuery->with('wallet')->latest()->get() : collect([]);
        $conversionRate = app(\App\Services\CryptoTradeRateService::class)->rate('USDT', 'sell') ?? 0;
        $marketRates = app(\App\Services\ExchangeRateService::class)->getUsdRates();

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
                'amount_usd' => $conversionRate > 0 ? ((float)$withdrawal->amount + (float)($withdrawal->fee_amount ?? 0)) / $conversionRate : null,
                'amount_ngn' => (float)$withdrawal->amount + (float)($withdrawal->fee_amount ?? 0),
                'status' => strtolower($withdrawal->status),
                'bank_account' => $bankAccount,
            ];
        });

        $depositTransactions = $deposits->map(function ($deposit) use ($conversionRate) { $ngn = max(0, (float)$deposit->amount - (float)($deposit->fee_amount ?? 0)); return ['created_at'=>$deposit->created_at, 'type'=>'deposit', 'coin'=>'NGN wallet', 'amount_usd'=>$conversionRate > 0 ? $ngn / $conversionRate : null, 'amount_ngn'=>$ngn, 'status'=>strtolower((string)$deposit->status), 'bank_account'=>null]; });
        $cryptoDepositTransactions = $cryptoDeposits->map(function ($deposit) use ($conversionRate, $marketRates) { $coin=strtoupper((string)$deposit->currency); $crypto=(float)($deposit->received_amount ?: $deposit->expected_amount); $usd=$crypto*(float)($marketRates[$coin] ?? 0); $ngn=(float)($deposit->ngn_credited_amount ?? 0); return ['created_at'=>$deposit->created_at, 'type'=>'deposit', 'coin'=>$coin.' crypto wallet', 'amount_usd'=>$usd > 0 ? $usd : null, 'amount_ngn'=>$ngn > 0 ? $ngn : ($usd > 0 && $conversionRate > 0 ? $usd*$conversionRate : null), 'status'=>strtolower((string)$deposit->status), 'bank_account'=>null]; });
        $cryptoWithdrawalTransactions = $cryptoWithdrawals->map(function ($withdrawal) use ($conversionRate, $marketRates) { $coin=strtoupper((string)($withdrawal->wallet?->currency ?? 'CRYPTO')); $usd=(float)($withdrawal->usd_value_at_request ?? 0); if ($usd <= 0) $usd=(float)$withdrawal->amount*(float)($marketRates[$coin] ?? 0); return ['created_at'=>$withdrawal->created_at, 'type'=>'withdrawal', 'coin'=>$coin.' crypto wallet', 'amount_usd'=>$usd > 0 ? $usd : null, 'amount_ngn'=>$usd > 0 && $conversionRate > 0 ? $usd*$conversionRate : null, 'status'=>strtolower((string)$withdrawal->status), 'bank_account'=>$withdrawal->network ?: 'Crypto wallet']; });

        // Combine transactions using concat
        $transactions = $buyTransactions->concat($sellTransactions)->concat($withdrawalTransactions)->concat($depositTransactions)->concat($cryptoDepositTransactions)->concat($cryptoWithdrawalTransactions)
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
