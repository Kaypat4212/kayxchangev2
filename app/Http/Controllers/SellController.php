<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SellTrade;
use App\Models\CryptoRate;
use App\Models\User;
use App\Services\AdminTradeAlertService;
use App\Mail\TradeNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SellController extends Controller
{
    private const UNVERIFIED_SELL_LIMIT_NGN = 500000;

    /**
     * Send a Telegram notification for a sell trade.
     *
     * @param string $message The message to send
     * @return bool True if notification sent successfully, false otherwise
     */
    private function sendTelegramNotification($message)
    {
        $botToken = config('services.telegram.token');
        $chatId = config('services.telegram.chat_id');

        Log::info('Telegram credentials check', [
        'bot_token' => $botToken ? 'set' : 'missing',
        'chat_id' => $chatId ? 'set' : 'missing',
    ]);
    
        if (!$botToken || !$chatId) {
            Log::error('Telegram notification failed: Missing credentials', [
                'bot_token' => $botToken ? 'set' : 'missing',
                'chat_id' => $chatId ? 'set' : 'missing',
                'env_file_exists' => file_exists(base_path('.env')),
                'env_content_sample' => file_exists(base_path('.env')) ? substr(file_get_contents(base_path('.env')), 0, 100) : 'missing',
            ]);
            return false;
        }

        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

        try {
            $response = Http::timeout(10)
                ->withOptions(['verify' => true])
                ->post($url, [
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'HTML',
                ]);

            if ($response->successful()) {
                Log::info('Telegram notification sent', ['response' => $response->body()]);
                return true;
            } else {
                Log::error('Telegram notification failed: API error', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Telegram notification failed: Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Get Paystack bank code from bank name.
     *
     * @param string $bankName The name of the bank
     * @return string|null The bank code if found, null otherwise
     */
    private function getBankCode($bankName)
    {
        $paystackSecretKey = env('PAYSTACK_SECRET_KEY');
        if (!$paystackSecretKey) {
            Log::error('Paystack bank code retrieval failed: Missing secret key');
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $paystackSecretKey,
            ])->timeout(10)->get('https://api.paystack.co/bank');

            if ($response->successful()) {
                $banks = $response->json('data', []);
                foreach ($banks as $bank) {
                    if (strtolower($bank['name']) === strtolower($bankName)) {
                        Log::info('Paystack bank code found', [
                            'bank_name' => $bankName,
                            'bank_code' => $bank['code'],
                        ]);
                        return $bank['code'];
                    }
                }
                Log::warning('Paystack bank code not found', ['bank_name' => $bankName]);
                return null;
            } else {
                Log::error('Paystack bank code retrieval failed: API error', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Paystack bank code retrieval failed: Exception', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Validate bank account details via Paystack.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * AJAX: return the Paystack bank list (lazy-loaded by the External Bank UI).
     */
    public function fetchBanks()
    {
        $key = config('paystack.secret_key') ?: env('PAYSTACK_SECRET_KEY');
        if (!$key) {
            return response()->json(['banks' => [], 'error' => 'Payment gateway not configured.']);
        }

        $banks = Cache::remember('paystack_banks_ng', 21600, function () use ($key) {
            try {
                $response = Http::withHeaders(['Authorization' => 'Bearer ' . $key])
                    ->timeout(10)
                    ->get('https://api.paystack.co/bank', [
                        'country' => 'nigeria',
                        'perPage' => 200,
                        'use_cursor' => false,
                    ]);

                if ($response->successful()) {
                    $data = $response->json('data', []);
                    usort($data, fn($a, $b) => strcmp($a['name'] ?? '', $b['name'] ?? ''));
                    return $data;
                }

                Log::error('fetchBanks: Paystack returned non-200', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return null; // null = don't cache failure
            } catch (\Exception $e) {
                Log::error('fetchBanks failed', ['error' => $e->getMessage()]);
                return null;
            }
        });

        if (empty($banks)) {
            // Remove bad value from cache so next request retries fresh
            Cache::forget('paystack_banks_ng');
            return response()->json(['banks' => [], 'error' => 'Could not load banks. Please retry.'], 503);
        }

        return response()->json(['banks' => $banks]);
    }

    public function validateBank(Request $request)
    {
        $request->validate([
            'bank_name'     => 'required|string|max:255',   // bank CODE sent from JS
            'alt_bank_name' => 'nullable|string|max:255',  // human-readable bank name
            'account_number' => 'required|string|regex:/^\d{10}$/',
        ]);

        $bankCode = $request->bank_name; // JS sends the code as bank_name
        $bankDisplayName = $request->input('alt_bank_name') ?: $bankCode; // Human-readable name
        if (!$bankCode) {
            return response()->json(['error' => 'Invalid bank name.'], 400);
        }

        $paystackSecretKey = env('PAYSTACK_SECRET_KEY');
        if (!$paystackSecretKey) {
            Log::error('Paystack validation failed: Missing secret key');
            return response()->json(['error' => 'Payment gateway error.'], 500);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $paystackSecretKey,
            ])->timeout(8)->get('https://api.paystack.co/bank/resolve', [
                'account_number' => $request->account_number,
                'bank_code' => $bankCode,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['status']) && $data['status'] && isset($data['data']['account_name'])) {
                    Session::put('sell.validated_bank', [
                        'bank_name'      => $bankDisplayName,
                        'account_number' => $request->account_number,
                        'account_name'   => $data['data']['account_name'],
                    ]);
                    Log::info('Bank account validated', [
                        'bank_name'      => $bankDisplayName,
                        'account_number' => $request->account_number,
                        'account_name'   => $data['data']['account_name'],
                    ]);
                    return response()->json([
                        'account_name' => $data['data']['account_name'],
                    ]);
                } else {
                    Log::error('Paystack validation failed: Invalid response', ['response' => $data]);
                    return response()->json(['error' => 'Unable to validate account.'], 400);
                }
            } else {
                Log::error('Paystack validation failed: API error', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                return response()->json(['error' => 'Failed to validate account.'], 400);
            }
        } catch (\Exception $e) {
            Log::error('Paystack validation failed: Exception', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Failed to validate account: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display step 1 of the sell process (coin and amount selection).
     *
     * @return \Illuminate\View\View
     */
    public function step1()
    {
        Log::info('Accessing sell.step1');
        $rates = CryptoRate::whereIn('coin', ['BTC', 'ETH', 'USDT', 'SOL'])
            ->pluck('sell_rate', 'coin')->toArray();
        if (empty($rates)) {
            Log::warning('No rates found in crypto_rates table');
            return view('sell.step1', ['rates' => [], 'error' => 'No crypto rates available.']);
        }
        return view('sell.step1', compact('rates'));
    }

    /**
     * Handle submission of step 1 (coin and amount).
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postStep1(Request $request)
    {
        Log::info('postStep1 request data', $request->all());

        $request->validate([
            'coin' => 'required|in:BTC,ETH,USDT,SOL',
            'amount' => 'required|numeric|min:0.01',
            'input_type' => 'required|in:usd,naira',
            'usd_amount' => 'required|numeric|min:0.01',
            'naira_amount' => 'required|numeric|min:0.01',
            'network' => 'nullable|in:ERC20,TRC20,BEP20,SOL,BTC',
        ]);

        $coin = $request->coin;
        $inputAmount = $request->amount;
        $inputType = $request->input_type;
        $submittedUsdAmount = $request->usd_amount;
        $submittedNairaAmount = $request->naira_amount;

        $rate = CryptoRate::where('coin', $coin)->value('sell_rate') ?? 0;
        if ($rate <= 0) {
            Log::error('Invalid rate for coin', ['coin' => $coin]);
            return back()->withErrors(['coin' => 'Invalid rate for selected coin.']);
        }

        if ($inputType === 'usd') {
            $usdAmount = round($inputAmount, 2);
            $nairaAmount = round($inputAmount * $rate, 2);
        } else {
            $nairaAmount = round($inputAmount, 2);
            $usdAmount = round($inputAmount / $rate, 2);
        }

        if (abs($usdAmount - $submittedUsdAmount) > 0.01 || abs($nairaAmount - $submittedNairaAmount) > 0.01) {
            Log::error('Amount mismatch', [
                'submitted_usd' => $submittedUsdAmount,
                'calculated_usd' => $usdAmount,
                'submitted_naira' => $submittedNairaAmount,
                'calculated_naira' => $nairaAmount,
            ]);
            return back()->withErrors(['amount' => 'Submitted amounts do not match expected conversion.']);
        }

        if (!Auth::user()->kyc_verified && $nairaAmount > self::UNVERIFIED_SELL_LIMIT_NGN) {
            return back()->withErrors([
                'amount' => 'Unverified users can sell up to ₦' . number_format(self::UNVERIFIED_SELL_LIMIT_NGN, 0) . ' per trade. Please complete KYC to increase your limit.'
            ])->withInput();
        }

        // Resolve network
        $network = $request->network;
        if (empty($network)) {
            $network = match($coin) {
                'BTC'  => 'BTC',
                'ETH'  => 'ERC20',
                'USDT' => 'TRC20',
                'SOL'  => 'SOL',
                default => null,
            };
        }

        Session::put('sell', [
            'coin' => $coin,
            'network' => $network,
            'usd_amount' => $usdAmount,
            'naira_amount' => $nairaAmount,
            'input_type' => $inputType,
            'rate' => $rate,
            'rate_locked_at' => now()->timestamp,
            'rate_expires_at' => now()->addMinutes(15)->timestamp,
        ]);
        Log::info('postStep1 session data set', Session::get('sell'));

        return redirect()->route('sell.step2');
    }

    /**
     * Display step 2 of the sell process (proof upload).
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function step2()
    {
        Log::info('Accessing sell.step2', ['session_data' => Session::get('sell') ?: []]);

        if (!Session::has('sell.coin') || !Session::has('sell.usd_amount') || !Session::has('sell.naira_amount')) {
            Log::warning('Step 2 accessed without step 1 data');
            return redirect()->route('sell.step1')->with('error', 'Please complete step 1 first.');
        }

        // Rate lock expiry check
        $rateExpiresAt = Session::get('sell.rate_expires_at');
        if ($rateExpiresAt && now()->timestamp > $rateExpiresAt) {
            Session::forget('sell');
            return redirect()->route('sell.step1')->with('error', 'Your locked rate has expired (15 minutes). Please start again with the current rate.');
        }

        $coin = Session::get('sell.coin');
        $network = Session::get('sell.network');
        $amountInUsd = Session::get('sell.usd_amount');
        $nairaAmount = Session::get('sell.naira_amount');

        $walletMap = config('wallets', []);
        $walletKey = ($coin === 'USDT' && $network) ? "USDT_{$network}" : $coin;
        $walletAddress = $walletMap[$walletKey] ?? $walletMap[$coin] ?? 'N/A';

        $proofPath = Session::get('sell.proof');
        $proofUrl = $proofPath ? asset('storage/' . $proofPath) : null;

        return view('sell.step2', compact('coin', 'amountInUsd', 'nairaAmount', 'walletAddress', 'proofUrl'));
    }

    /**
     * Handle submission of step 2 (proof upload).
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postStep2(Request $request)
    {
        Log::info('postStep2 request data', $request->all());

        $request->validate([
            'proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        try {
            $proofPath = $request->file('proof')->store('payment_proofs', 'public');
            Log::info('Proof uploaded', ['path' => $proofPath]);
            Session::put('sell.proof', $proofPath);
            Log::info('postStep2 session data updated', Session::get('sell'));
        } catch (\Exception $e) {
            Log::error('File upload error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withErrors(['proof' => 'Failed to upload proof.']);
        }

        return redirect()->route('sell.step3');
    }

    /**
     * Display step 3 of the sell process (payout method selection).
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function step3()
    {
        Log::info('Accessing sell.step3', ['session_data' => Session::get('sell') ?: []]);

        if (!Session::has('sell.proof') || !Session::has('sell.coin') || !Session::has('sell.usd_amount') || !Session::has('sell.naira_amount')) {
            Log::warning('Step 3 accessed without required session data');
            return redirect()->route('sell.step1')->with('error', 'Please complete previous steps.');
        }

        // Rate lock expiry check
        $rateExpiresAt = Session::get('sell.rate_expires_at');
        if ($rateExpiresAt && now()->timestamp > $rateExpiresAt) {
            Session::forget('sell');
            return redirect()->route('sell.step1')->with('error', 'Your locked rate has expired (15 minutes). Please start again with the current rate.');
        }

        $user = Auth::user();
        $nairaAmount = Session::get('sell.naira_amount');
        $balance = $user->balance ?? 0;

        $userData = [
            'bank_name' => $user->bank_name ?? 'N/A',
            'account_number' => $user->account_number ?? 'N/A',
            'account_name' => $user->account_name ?? 'N/A',
            'balance' => $balance,
        ];

        // Banks are loaded lazily via AJAX when the user selects "External Bank".
        // Loading them here would block the page for 10-14 seconds if Paystack is slow.
        $banks = [];

        return view('sell.step3', compact('userData', 'balance', 'nairaAmount', 'banks'));
    }

    /**
     * Finalize the sell trade.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function finalize(Request $request)
    {
        Log::info('Finalize request data', $request->all());

        $requiredSessionKeys = ['sell.coin', 'sell.usd_amount', 'sell.naira_amount', 'sell.proof'];
        foreach ($requiredSessionKeys as $key) {
            if (!Session::has($key)) {
                Log::error('Missing session key', ['key' => $key]);
                return redirect()->route('sell.step1')->with('error', 'Please complete all previous steps.');
            }
        }

        Log::info('Finalize session data', Session::get('sell') ?: []);

        $request->validate([
            'payout_method' => 'required|in:default_bank,external_bank,wallet_balance',
            'password' => 'required|string|min:6',
        ]);

        $user = Auth::user();
        Log::info('User data for finalize', [
            'user_id' => $user->id,
            'bank_name' => $user->bank_name,
            'account_number' => $user->account_number,
            'account_name' => $user->account_name,
            'balance' => $user->balance,
        ]);

        if (!Hash::check($request->password, $user->password)) {
            Log::warning('Password validation failed', ['user_id' => $user->id]);
            return back()->withErrors(['password' => 'Incorrect password.']);
        }

        if ($request->payout_method === 'external_bank') {
            if (!Session::has('sell.validated_bank')) {
                Log::error('External bank selected without validated bank details');
                return back()->withErrors(['payout_method' => 'Please validate bank details before proceeding.']);
            }
            $validatedBank = Session::get('sell.validated_bank');
        }

        DB::beginTransaction();

        try {
            $nairaAmount = (float) Session::get('sell.naira_amount');
            if (!$user->kyc_verified && $nairaAmount > self::UNVERIFIED_SELL_LIMIT_NGN) {
                throw new \Exception('Unverified users can sell up to ₦' . number_format(self::UNVERIFIED_SELL_LIMIT_NGN, 0) . ' per trade. Please complete KYC to increase your limit.');
            }

            $sellTrade = new SellTrade();
            $sellTrade->user_id = $user->id;
            $sellTrade->coin = Session::get('sell.coin');
            $sellTrade->usd_amount = Session::get('sell.usd_amount');
            $sellTrade->naira_amount = Session::get('sell.naira_amount');
            $sellTrade->rate_used = Session::get('sell.rate');
            $sellTrade->proof = Session::get('sell.proof');
            $sellTrade->payment_method = $request->payout_method;
            $sellTrade->status = 'pending';
            $sellTrade->name = $user->name ?? $user->email ?? 'Unknown';
            $sellTrade->transaction_ref = 'SELL-' . Str::upper(Str::random(10));

            $walletMap = config('wallets', []);

            $coin    = Session::get('sell.coin');
            $network = Session::get('sell.network');

            // Pick the most specific wallet key
            $walletKey = ($coin === 'USDT' && $network)
                ? "USDT_{$network}"
                : $coin;

            $sellTrade->network       = $network;
            $sellTrade->wallet_address = $walletMap[$walletKey] ?? $walletMap[$coin] ?? 'N/A';

            if ($request->payout_method === 'default_bank') {
                if (empty($user->bank_name) || empty($user->account_number) || empty($user->account_name) || $user->bank_name === 'N/A' || $user->account_number === 'N/A' || $user->account_name === 'N/A') {
                    Log::error('Incomplete default bank details', [
                        'user_id' => $user->id,
                        'bank_name' => $user->bank_name,
                        'account_number' => $user->account_number,
                        'account_name' => $user->account_name,
                    ]);
                    throw new \Exception('Your profile does not have valid bank details. Please update your bank information or choose another payout method.');
                }
                $sellTrade->bank_name = $user->bank_name;
                $sellTrade->account_number = $user->account_number;
                $sellTrade->account_name = $user->account_name;
            } elseif ($request->payout_method === 'external_bank') {
                $sellTrade->bank_name = $validatedBank['bank_name'];
                $sellTrade->account_number = $validatedBank['account_number'];
                $sellTrade->account_name = $validatedBank['account_name'];
            } else {
                $sellTrade->bank_name = 'WALLET BALANCE';
                $sellTrade->account_number = 'N/A';
                $sellTrade->account_name = $user->name ?? $user->email;
            }

            Log::info('Attempting to save SellTrade', $sellTrade->toArray());
            try {
                $sellTrade->save();
                Log::info('SellTrade saved successfully', ['trade_id' => $sellTrade->id]);
            } catch (\Exception $e) {
                Log::error('Failed to save SellTrade', [
                    'error' => $e->getMessage(),
                    'attributes' => $sellTrade->toArray(),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw new \Exception('Database error: ' . $e->getMessage());
            }

            DB::commit();

            $rate = Session::get('sell.rate') ?? CryptoRate::where('coin', $sellTrade->coin)->value('sell_rate') ?? 'N/A';
            $tradeTimestamp = $sellTrade->created_at->setTimezone('Africa/Lagos')->format('Y-m-d H:i:s T');

            $message = "<b>New Sell Trade</b>\n";
            $message .= "Trade ID: {$sellTrade->id}\n";
            $message .= "Ref: {$sellTrade->transaction_ref}\n";
            $message .= "User: {$sellTrade->name}\n";
            $message .= "Coin: {$sellTrade->coin}\n";
            $message .= "USD: \${$sellTrade->usd_amount}\n";
            $message .= "Naira: ₦{$sellTrade->naira_amount}\n";
            $message .= "Rate: ₦{$rate}/USD\n";
            $message .= "User Balance: ₦" . number_format($user->balance, 2) . "\n";
            $message .= "Method: {$sellTrade->payment_method}\n";
            if ($request->payout_method !== 'wallet_balance') {
                $message .= "Bank: {$sellTrade->bank_name}\n";
                $message .= "Account: {$sellTrade->account_number}\n";
                $message .= "Name: {$sellTrade->account_name}\n";
            }
            $message .= "Wallet: {$sellTrade->wallet_address}\n";
            $message .= "Proof: <a href=\"" . asset('storage/' . $sellTrade->proof) . "\">View</a>\n";
            $message .= "Status: {$sellTrade->status}\n";
            $message .= "Timestamp: {$tradeTimestamp}\n";

            // Admin alert: Telegram + in-app broadcast notification badge
            try {
                app(AdminTradeAlertService::class)->sendTriggeredAlert('sell', [
                    'user_id' => $user->id,
                    'reference' => $sellTrade->transaction_ref,
                    'user_name' => $sellTrade->name,
                    'user_email' => $user->email,
                    'coin' => $sellTrade->coin,
                    'usd_amount' => number_format((float) $sellTrade->usd_amount, 6),
                    'naira_amount' => number_format((float) $sellTrade->naira_amount, 2),
                    'wallet_address' => $sellTrade->wallet_address ?? 'N/A',
                    'network' => $sellTrade->network ?? 'N/A',
                    'status' => $sellTrade->status,
                ]);
            } catch (\Throwable $alertEx) {
                Log::warning('Sell trade admin alert failed: ' . $alertEx->getMessage());
            }

            Session::forget('sell');
            Log::info('Session cleared');

            // Send sell trade submitted email
            try {
                $payoutMethodLabel = match ($sellTrade->payment_method) {
                    'default_bank', 'external_bank' => ($sellTrade->bank_name . ' — ' . $sellTrade->account_number),
                    'wallet_balance'                 => 'Wallet Balance',
                    default                          => $sellTrade->payment_method,
                };
                Mail::to($user->email)->send(new TradeNotification(
                    user: $user,
                    templateKey: 'sell_trade_submitted',
                    data: [
                        'amount'         => number_format((float)$sellTrade->usd_amount, 6),
                        'currency'       => $sellTrade->coin,
                        'naira_amount'   => number_format((float)$sellTrade->naira_amount, 2),
                        'reference'      => $sellTrade->transaction_ref,
                        'payment_method' => $payoutMethodLabel,
                    ],
                    badge: ['text' => 'Sell Order Received', 'color' => '#f0a500'],
                    ctaUrl: route('trade.summary', ['trade_id' => $sellTrade->id]),
                    ctaText: 'View Order Summary',
                ));
            } catch (\Exception $mailEx) {
                Log::warning('Sell trade submitted email failed: ' . $mailEx->getMessage());
            }

            return redirect()->route('trade.summary', ['trade_id' => $sellTrade->id])
                ->with('success', 'Trade submitted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Finalize error', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'session_data' => Session::get('sell') ?: [],
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('sell.step3')->withErrors(['payout_method' => $e->getMessage()]);
        }
    }

    /**
     * Display the trade summary page.
     *
     * @param int $trade_id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function tradeSummary($trade_id)
    {
        Log::info('Accessing trade.summary', ['trade_id' => $trade_id]);

        try {
            $trade = SellTrade::where('id', $trade_id)
                ->where('user_id', Auth::id())
                ->firstOrFail();
        } catch (\Exception $e) {
            Log::error('Trade summary error', ['error' => $e->getMessage()]);
            return redirect()->route('sell.step1')->with('error', 'Trade not found.');
        }

        return view('sell.summary', compact('trade'));
    }

    /**
     * Show the proof upload page for a sell trade (used for bot-submitted trades).
     */
    public function paymentPage(int $id)
    {
        $trade = SellTrade::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $walletAddress = config("wallets.{$trade->coin}", null);
        return view('sell.payment', compact('trade', 'walletAddress'));
    }

    /**
     * Handle proof upload for a sell trade (standalone, not wizard-based).
     */
    public function uploadPayment(Request $request, int $id)
    {
        $request->validate([
            'proof' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $trade = SellTrade::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        if ($trade->proof && $trade->proof !== 'bot_initiated') {
            Storage::disk('public')->delete($trade->proof);
        }

        $path         = $request->file('proof')->store('payment_proofs', 'public');
        $trade->proof = $path;
        $trade->save();

        Log::info('Sell proof uploaded via payment page', ['trade_id' => $id, 'user_id' => Auth::id()]);

        try {
            $this->sendTelegramNotification("📤 Sell proof uploaded for trade #{$id} by user #{$trade->user_id} ({$trade->coin})");
        } catch (\Throwable) {}

        return redirect()->route('trade.summary', $id)
            ->with('success', 'Proof uploaded! We will process your trade shortly.');
    }
}
