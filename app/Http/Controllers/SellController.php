<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SellTrade;
use App\Models\CryptoRate;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SellController extends Controller
{
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
    public function validateBank(Request $request)
    {
        $request->validate([
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|regex:/^\d{10}$/',
            'password' => 'required|string|min:6',
        ]);

        $user = Auth::user();
        if (!Hash::check($request->password, $user->password)) {
            Log::warning('Password validation failed', ['user_id' => $user->id]);
            return response()->json(['error' => 'Incorrect password.'], 400);
        }

        $bankCode = $this->getBankCode($request->bank_name);
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
            ])->timeout(10)->get('https://api.paystack.co/bank/resolve', [
                'account_number' => $request->account_number,
                'bank_code' => $bankCode,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['status']) && $data['status'] && isset($data['data']['account_name'])) {
                    Session::put('sell.validated_bank', [
                        'bank_name' => $request->bank_name,
                        'account_number' => $request->account_number,
                        'account_name' => $data['data']['account_name'],
                    ]);
                    Log::info('Bank account validated', [
                        'bank_name' => $request->bank_name,
                        'account_number' => $request->account_number,
                        'account_name' => $data['data']['account_name'],
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
        $rates = CryptoRate::pluck('sell_rate', 'coin')->toArray();
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
            'coin' => 'required|in:BTC,ETH,USDT',
            'amount' => 'required|numeric|min:0.01',
            'input_type' => 'required|in:usd,naira',
            'usd_amount' => 'required|numeric|min:0.01',
            'naira_amount' => 'required|numeric|min:0.01',
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

        Session::put('sell', [
            'coin' => $coin,
            'usd_amount' => $usdAmount,
            'naira_amount' => $nairaAmount,
            'input_type' => $inputType,
            'rate' => $rate, // Store the rate used for the trade
        ]);
        Log::info('postStep1 session data set', Session::get('sell'));

        return redirect()->route('sell.step2');
    }

    /**
     * Display step 2 of the sell process (proof upload).
     *
     * @return \Illuminate\View\View
     */
    public function step2()
    {
        Log::info('Accessing sell.step2', ['session_data' => Session::get('sell') ?: []]);

        if (!Session::has('sell.coin') || !Session::has('sell.usd_amount') || !Session::has('sell.naira_amount')) {
            Log::warning('Step 2 accessed without step 1 data');
            return redirect()->route('sell.step1')->with('error', 'Please complete step 1 first.');
        }

        $coin = Session::get('sell.coin');
        $amountInUsd = Session::get('sell.usd_amount');
        $nairaAmount = Session::get('sell.naira_amount');

        $walletMap = config('wallets') ?? [
            'BTC' => '1K3uPpiJRi4UkTYBEniPMejxsCMxbygqyN',
            'ETH' => '0x42c9accd6679f54e8ddbc9383ca80d7319820b67',
            'USDT' => 'TQhLfKnkQRcn5k6xye6sU1LV6rFmb1nBPQ',
        ];
        $walletAddress = $walletMap[$coin] ?? 'N/A';

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
     * @return \Illuminate\View\View
     */
    public function step3()
    {
        Log::info('Accessing sell.step3', ['session_data' => Session::get('sell') ?: []]);

        if (!Session::has('sell.proof') || !Session::has('sell.coin') || !Session::has('sell.usd_amount') || !Session::has('sell.naira_amount')) {
            Log::warning('Step 3 accessed without required session data');
            return redirect()->route('sell.step1')->with('error', 'Please complete previous steps.');
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

        $paystackSecretKey = env('PAYSTACK_SECRET_KEY');
        $banks = [];
        if ($paystackSecretKey) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $paystackSecretKey,
                ])->timeout(10)->get('https://api.paystack.co/bank');
                if ($response->successful()) {
                    $banks = $response->json('data', []);
                }
            } catch (\Exception $e) {
                Log::error('Failed to fetch Paystack banks', ['error' => $e->getMessage()]);
            }
        }

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
            $sellTrade = new SellTrade();
            $sellTrade->user_id = $user->id;
            $sellTrade->coin = Session::get('sell.coin');
            $sellTrade->usd_amount = Session::get('sell.usd_amount');
            $sellTrade->naira_amount = Session::get('sell.naira_amount');
            $sellTrade->proof = Session::get('sell.proof');
            $sellTrade->payment_method = $request->payout_method;
            $sellTrade->status = 'pending';
            $sellTrade->name = $user->name ?? $user->email ?? 'Unknown';
            $sellTrade->transaction_ref = 'SELL-' . Str::upper(Str::random(10));

            $walletMap = config('wallets') ?? [
                'BTC' => '1K3uPpiJRi4UkTYBEniPMejxsCMxbygqyN',
                'ETH' => '0x42c9accd6679f54e8ddbc9383ca80d7319820b67',
                'USDT' => 'TQhLfKnkQRcn5k6xye6sU1LV6rFmb1nBPQ',
            ];
            $sellTrade->wallet_address = $walletMap[Session::get('sell.coin')] ?? 'N/A';

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

            if (!$this->sendTelegramNotification($message)) {
                Log::warning('Telegram notification failed', ['trade_id' => $sellTrade->id]);
            }

            Session::forget('sell');
            Log::info('Session cleared');

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
     * @return \Illuminate\View\View
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
}
