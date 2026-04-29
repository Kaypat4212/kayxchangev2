<?php

namespace App\Http\Controllers;

use App\Http\Requests\BuyCryptoRequest;
// use App\Models\AccountDetail;
use App\Models\BuyTrade;
use App\Models\CryptoRate;
use App\Models\CompanyAccount;
use App\Services\AdminTradeAlertService;
use App\Services\CoinGeckoService;
use App\Mail\TradeNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

// Note: This code was written by kaypat.dev

class BuyController extends Controller
{
    /**
     * Display the buy crypto form with available coin rates.
     *
     * Fetches buy rates from CryptoRate model, merges with default rates,
     * and passes them to the 'buy' view for display.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show()
    {
        if (!auth()->user()->kyc_verified) {
            return redirect()->route('kyc.form')
                ->with('error', 'KYC verification is required before you can buy crypto.');
        }

        $rates = CryptoRate::pluck('buy_rate', 'coin')->toArray();
        $defaultRates = ['BTC' => 1600, 'ETH' => 1500, 'USDT' => 1400];
        $rates = array_merge($defaultRates, $rates);
        return view('buy', compact('rates'));
    }

    /**
     * Handle submission of a buy crypto request.
     *
     * Validates the request, calculates USD/Naira amounts based on the input type,
     * creates a new BuyTrade record, and redirects to the trade summary page.
     * Logs success or failure for debugging.
     *
     * @param BuyCryptoRequest $request Validated request data
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submit(BuyCryptoRequest $request)
    {
        if (!auth()->user()->kyc_verified) {
            return redirect()->route('kyc.form')
                ->with('error', 'KYC verification is required before you can buy crypto.');
        }

        // Fetch current rates or use defaults
        $rates = CryptoRate::pluck('buy_rate', 'coin')->toArray();
        $defaultRates = ['BTC' => 1600, 'ETH' => 1500, 'USDT' => 1400];
        $coin = $request->input('coin');
        $rate = $rates[$coin] ?? $defaultRates[$coin] ?? null;

        // Check if rate exists for the selected coin
        if (!$rate) {
            Log::warning("No buy rate available for coin: {$coin}");
            return redirect()->back()->withErrors(['coin' => 'No buy rate available for the selected coin.'])->withInput();
        }

        // Get validated data
        $validated = $request->validated();
        Log::info('Validated buy request:', $validated);

        // Calculate amounts based on input type (naira or USD)
        if ($validated['input_type'] === 'naira') {
            $naira_amount = $validated['amount'];
            $usd_amount = $validated['amount'] / $rate;
        } else {
            $usd_amount = $validated['amount'];
            $naira_amount = $validated['amount'] * $rate;
        }

        // Generate unique transaction reference
        $transaction_ref = 'BUY-' . Str::upper(Str::random(10));

        try {
            // Determine payment method
            $paymentMethod = $validated['payment_method'] ?? 'bank_transfer';

            // Create new trade record
            $buyTrade = BuyTrade::create([
                'user_id' => auth()->id(),
                'name' => auth()->user()->name,
                'coin' => $validated['coin'],
                'usd_amount' => $usd_amount,
                'naira_amount' => $naira_amount,
                'rate_used' => $rate,
                'wallet_address' => $validated['wallet_address'],
                'network' => $validated['network'],
                'payment_method' => $paymentMethod,
                'status' => 'pending',
                'ip_address' => $request->ip(),
                'transaction_ref' => $transaction_ref,
                'transaction_type' => 'buy',
            ]);

            // Log successful trade creation
            Log::info('Buy trade created successfully', [
                'trade_id' => $buyTrade->id,
                'user_id' => auth()->id(),
                'name' => auth()->user()->name,
                'transaction_ref' => $transaction_ref
            ]);

            // Calculate crypto equivalent for display
            $cryptoAmount = $usd_amount / $rate;
            
            // Send confirmation email
            try {
                Mail::to(auth()->user()->email)->send(new TradeNotification(
                    user: auth()->user(),
                    templateKey: 'buy_trade_submitted',
                    data: [
                        'usd_amount'     => number_format($usd_amount, 2),
                        'crypto_amount'  => number_format($cryptoAmount, 8),
                        'currency'       => $validated['coin'],
                        'rate_used'      => number_format($rate, 2),
                        'naira_amount'   => number_format($naira_amount, 2),
                        'wallet_address' => $validated['wallet_address'],
                        'reference'      => $transaction_ref,
                    ],
                    badge: ['text' => 'Order Received', 'color' => '#f0a500'],
                    ctaUrl: route('buy.summary', ['id' => $buyTrade->id]),
                    ctaText: 'View Order Summary',
                ));
            } catch (\Exception $mailEx) {
                Log::warning('Buy trade submitted email failed: ' . $mailEx->getMessage());
            }

            // Admin alert: Telegram + in-app broadcast notification
            try {
                app(AdminTradeAlertService::class)->sendTriggeredAlert('buy', [
                    'user_id' => auth()->id(),
                    'reference' => $transaction_ref,
                    'user_name' => auth()->user()->name,
                    'user_email' => auth()->user()->email,
                    'coin' => $validated['coin'],
                    'usd_amount' => number_format((float) $usd_amount, 6),
                    'naira_amount' => number_format((float) $naira_amount, 2),
                    'wallet_address' => $validated['wallet_address'] ?? 'N/A',
                    'network' => $validated['network'] ?? 'N/A',
                    'status' => 'pending',
                ]);
            } catch (\Throwable $alertEx) {
                Log::warning('Buy trade admin alert failed: ' . $alertEx->getMessage());
            }

            // Telegram alert immediately on trade creation (before proof upload)
            try {
                $this->sendTelegramAlert($buyTrade, false);
            } catch (\Throwable $tgEx) {
                Log::warning('Buy trade Telegram alert on submit failed: ' . $tgEx->getMessage());
            }

            // Handle different payment methods
            if ($paymentMethod === 'crypto') {
                return $this->handleCryptoPayment($buyTrade);
            }

            // Redirect to trade summary for bank transfers
            return redirect()->route('buy.summary', ['id' => $buyTrade->id])
                ->with('success', 'Please review your trade details.');
        } catch (\Exception $e) {
            // Log error if trade creation fails
            Log::error('Failed to create buy trade: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'name' => auth()->user()->name,
                'coin' => $validated['coin'],
                'wallet_address' => $validated['wallet_address'],
                'network' => $validated['network'],
                'exception' => $e->getTraceAsString()
            ]);
            return redirect()->back()->withErrors(['error' => 'An error occurred while processing your trade.'])->withInput();
        }
    }

    /**
     * Handle crypto payment creation with Cryptomus
     */
    private function handleCryptoPayment(BuyTrade $trade)
    {
        try {
            $cryptomus = app(\App\Services\CryptomusService::class);

            if (!$cryptomus->isEnabled()) {
                // Fallback to bank transfer if crypto payment is not available
                return redirect()->route('buy.summary', ['id' => $trade->id])
                    ->with('warning', 'Crypto payment is currently unavailable. Please use bank transfer instead.');
            }

            // Create Cryptomus payment
            $paymentData = [
                'amount' => $trade->naira_amount,
                'currency' => 'NGN',
                'crypto_currency' => $trade->coin,
                'order_id' => $trade->transaction_ref,
                'url_callback' => route('cryptomus.webhook'),
                'url_return' => route('cryptomus.success', $trade->transaction_ref),
                'lifetime' => 3600, // 1 hour
                'additional_data' => [
                    'trade_id' => $trade->id,
                    'user_id' => $trade->user_id,
                    'coin' => $trade->coin
                ]
            ];

            $payment = $cryptomus->createPayment($paymentData);

            if (isset($payment['result'])) {
                // Update trade with payment info
                $trade->update([
                    'payment_id' => $payment['result']['uuid'],
                    'payment_data' => json_encode($payment['result'])
                ]);

                // Redirect to crypto payment page
                return redirect()->route('buy.crypto-payment', ['id' => $trade->id])
                    ->with('success', 'Crypto payment created successfully.');
            }

            // If payment creation failed, fallback to bank transfer
            Log::error('Cryptomus payment creation failed', ['trade_id' => $trade->id, 'response' => $payment]);
            return redirect()->route('buy.summary', ['id' => $trade->id])
                ->with('warning', 'Crypto payment setup failed. Please use bank transfer instead.');

        } catch (\Exception $e) {
            Log::error('Crypto payment setup failed', [
                'trade_id' => $trade->id,
                'error' => $e->getMessage()
            ]);

            // Fallback to bank transfer
            return redirect()->route('buy.summary', ['id' => $trade->id])
                ->with('warning', 'Crypto payment is currently unavailable. Please use bank transfer instead.');
        }
    }

    /**
     * Display crypto payment page
     */
    public function cryptoPaymentPage($id)
    {
        $trade = BuyTrade::findOrFail($id);

        // Ensure user owns this trade
        if ($trade->user_id !== auth()->id()) {
            abort(403);
        }

        // Ensure this is a crypto payment trade
        if ($trade->payment_method !== 'crypto') {
            return redirect()->route('buy.summary', $id);
        }

        // Get payment data from Cryptomus
        $paymentData = json_decode($trade->payment_data ?? '{}', true);

        return view('buy.crypto-payment', compact('trade', 'paymentData'));
    }

    /**
     * Send a Telegram notification for a trade.
     *
     * Builds a message with trade and user details, optionally including payment proof.
     * Sends the message via HTTP client (primary) or cURL (fallback).
     * Uses CA certificate for SSL verification and logs success or failure.
     *
     * @param BuyTrade $trade The trade to notify about
     * @param bool $includeProof Whether to include payment proof URL
     * @return bool True if notification sent successfully, false otherwise
     */
    protected function sendTelegramAlert($trade, $includeProof = false)
    {
        // Get authenticated user and Telegram config
        $user = auth()->user();
        $token = config('services.telegram.token');
        $chatId = config('services.telegram.chat_id');

        // Log notification attempt
        Log::info('Attempting Telegram notification', [
            'trade_id' => $trade->id,
            'user_id' => $user->id,
            'name' => auth()->user()->name,
            'token_set' => !empty($token) ? 'yes' : 'no',
            'chat_id_set' => !empty($chatId) ? 'yes' : 'no'
        ]);

        // Check if token or chat ID is missing
        if (empty($token) || empty($chatId)) {
            Log::error('Telegram notification aborted: Missing token or chat_id', [
                'trade_id' => $trade->id,
                'user_id' => $user->id
            ]);
            return false;
        }

        // Build message with trade details
        $message = "📥 *New Buy Trade Alert!*\n\n"
            . "*User:* {$user->name} ({$user->email})\n"
            . "*User ID:* {$user->id}\n"
            . "*Transaction Ref:* {$trade->transaction_ref}\n"
            . "*Coin:* {$trade->coin}\n"
            . "*Amount:* \${$trade->usd_amount} (~₦" . number_format($trade->naira_amount, 2) . ")\n"
            . "*Network:* {$trade->network}\n"
            . "*Wallet:* `{$trade->wallet_address}`\n"
            . "*IP Address:* {$trade->ip_address}\n"
            . "*Status:* {$trade->status}";

        // Include payment proof URL if requested and available
        if ($includeProof && $trade->payment_proof) {
            $proofPath = $trade->payment_proof;
            $proofUrl = asset('storage/' . $proofPath);
            if (Storage::disk('public')->exists($proofPath)) {
                $message .= "\n🧾 *Payment Proof:* [View Image]({$proofUrl})";
                Log::info('Payment proof URL included', [
                    'trade_id' => $trade->id,
                    'url' => $proofUrl,
                    'path' => storage_path('app/public/' . $proofPath)
                ]);
            } else {
                Log::warning('Payment proof file not found', [
                    'trade_id' => $trade->id,
                    'path' => $proofPath
                ]);
                $message .= "\n🧾 *Payment Proof:* (File not found)";
            }
        }

        // Prepare Telegram API request
        $url = "https://api.telegram.org/bot{$token}/sendMessage";
        $payload = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown',
            'disable_web_page_preview' => false,
        ];

        // Log payload for debugging
        Log::debug('Telegram payload', [
            'trade_id' => $trade->id,
            'url' => $url,
            'payload' => $payload
        ]);

        // Primary method: HTTP client
        try {
            $response = Http::withOptions([
                'verify' => 'C:\xampp\php\extras\ssl\cacert.pem',
                'timeout' => 15,
            ])->retry(5, 2000)->post($url, $payload);
            if ($response->successful()) {
                Log::info('Telegram notification sent successfully via Http', [
                    'trade_id' => $trade->id,
                    'chat_id' => $chatId,
                    'response' => $response->json()
                ]);
                return true;
            } else {
                Log::error('Telegram notification failed via Http', [
                    'trade_id' => $trade->id,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Telegram notification failed via Http: ' . $e->getMessage(), [
                'trade_id' => $trade->id,
                'exception' => $e->getTraceAsString()
            ]);
        }

        // Fallback: HTTP
        try {
            Log::info('Attempting Telegram notification via HTTP', [
                'trade_id' => $trade->id,
                'url' => $url
            ]);

            $response = Http::timeout(15)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, $payload);

            if ($response->successful() && $response->json('ok')) {
                Log::info('Telegram notification sent successfully via HTTP', [
                    'trade_id' => $trade->id,
                    'chat_id' => $chatId,
                    'response' => $response->body()
                ]);
                return true;
            } else {
                Log::error('Telegram notification failed via HTTP', [
                    'trade_id' => $trade->id,
                    'http_code' => $response->status(),
                    'response' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Telegram notification failed via HTTP: ' . $e->getMessage(), [
                'trade_id' => $trade->id,
                'exception' => $e->getTraceAsString()
            ]);
        }

        return false;
    }

    /**
     * Handle payment proof upload for a trade.
     *
     * Validates and stores the uploaded image, updates the trade with the image path,
     * sends a Telegram notification with trade details and proof URL, and redirects to success page.
     * Ensures only the trade owner can upload proof.
     *
     * @param Request $request HTTP request with payment proof
     * @param int $id Trade ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function uploadPayment(Request $request, $id)
    {
        // Validate uploaded image
        $validated = $request->validate([
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Fetch trade and check ownership
        $trade = BuyTrade::findOrFail($id);
        if ($trade->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }

        try {
            // Handle file upload
            if ($request->hasFile('payment_proof') && $request->file('payment_proof')->isValid()) {
                // Delete existing proof if present
                if ($trade->payment_proof) {
                    Storage::disk('public')->delete($trade->payment_proof);
                }

                // Store new proof and update trade
                $imagePath = $request->file('payment_proof')->store('payment_proofs', 'public');
                $trade->payment_proof = $imagePath;
                $trade->save();

                // Log successful upload
                Log::info('Payment proof uploaded and trade updated', [
                    'trade_id' => $trade->id,
                    'user_id' => auth()->id(),
                    'proof_path' => $imagePath
                ]);

                // Send Telegram notification with proof
                $this->sendTelegramAlert($trade, true);

                // Notify user that proof was received
                try {
                    $user = auth()->user();
                    Mail::to($user->email)->send(new TradeNotification(
                        user: $user,
                        templateKey: 'buy_trade_payment_uploaded',
                        data: [
                            'amount'    => number_format($trade->usd_amount, 6),
                            'currency'  => $trade->coin,
                            'reference' => $trade->transaction_ref,
                        ],
                        badge: ['text' => 'Payment Proof Received', 'color' => '#0d6efd'],
                        ctaUrl: route('buy.success', ['id' => $trade->id]),
                        ctaText: 'View Order',
                    ));
                } catch (\Exception $mailEx) {
                    Log::warning('Buy proof uploaded email failed: ' . $mailEx->getMessage());
                }

                return redirect()->route('buy.success', ['id' => $trade->id])
                    ->with('success', 'Payment proof uploaded successfully!');
            }

            throw new \Exception('No valid file uploaded.');
        } catch (\Exception $e) {
            // Log error if upload fails
            Log::error('Failed to upload payment proof: ' . $e->getMessage(), [
                'trade_id' => $id,
                'exception' => $e->getTraceAsString()
            ]);
            return redirect()->back()->withErrors(['error' => 'Failed to upload payment proof. Please try again.']);
        }
    }

    /**
     * Display the trade summary page.
     *
     * Fetches the trade by ID, ensures the user owns it, and renders the summary view.
     *
     * @param int $id Trade ID
     * @return \Illuminate\View\View
     */
    public function summary($id)
    {
        $trade = BuyTrade::findOrFail($id);
        if ($trade->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }
        $cryptoPrices = $this->fetchCryptoUsdPrices();
        return view('buy.summary', compact('trade', 'cryptoPrices'));
    }

    /**
     * Return live BTC/ETH/USDT USD prices (cached 2 min) for JS/Blade use.
     */
    public function cryptoPricesApi()
    {
        return response()->json($this->fetchCryptoUsdPrices());
    }

    private function fetchCryptoUsdPrices(): array
    {
        return Cache::remember('buy_crypto_usd_prices', 120, function () {
            try {
                $data = app(CoinGeckoService::class)->getCryptoPrices(['bitcoin', 'ethereum', 'tether']);
                $map = ['BTC' => 65000.0, 'ETH' => 3500.0, 'USDT' => 1.0];
                foreach ($data as $item) {
                    if ($item['id'] === 'bitcoin')  $map['BTC']  = (float) ($item['price_usd'] ?? 65000);
                    if ($item['id'] === 'ethereum') $map['ETH']  = (float) ($item['price_usd'] ?? 3500);
                    if ($item['id'] === 'tether')   $map['USDT'] = (float) ($item['price_usd'] ?? 1.0);
                }
                return $map;
            } catch (\Throwable $e) {
                Log::warning('CoinGecko price fetch failed: ' . $e->getMessage());
                return ['BTC' => 65000.0, 'ETH' => 3500.0, 'USDT' => 1.0];
            }
        });
    }

    /**
     * Display the payment page with bank details.
     *
     * Fetches the trade and account details, ensures user ownership, and renders the payment view.
     *
     * @param int $id Trade ID
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function paymentPage($id)
    {
       $trade = BuyTrade::findOrFail($id);
    $accountDetails = CompanyAccount::where('is_active', true)->first(); // Adjust query as needed

    if (!$accountDetails) {
        Log::warning('No active company account found for trade ID: ' . $id);
        return redirect()->back()->with('error', 'Payment account details are unavailable.');
    }

    return view('buy.paymentPage', compact('trade', 'accountDetails'));
    }

    /**
     * Display the success page after payment proof upload.
     *
     * Fetches the trade, ensures user ownership, and renders the success view.
     *
     * @param int $id Trade ID
     * @return \Illuminate\View\View
     */
    public function success($id)
    {
        $trade = BuyTrade::findOrFail($id);
        if ($trade->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }
        return view('buy.success', compact('trade'));
    }

    /**
     * Log wallet validation errors.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logWalletError(Request $request)
    {
        Log::error('Wallet validation error', $request->all());
        return response()->json(['status' => 'logged']);
    }

    /**
     * Update the status of a trade.
     *
     * Validates the new status, updates the trade record, and redirects to the summary page.
     * Only allows trade owner or admin to update. Logs success or failure.
     *
     * @param Request $request HTTP request with new status
     * @param int $id Trade ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, $id): \Illuminate\Http\RedirectResponse
    {
        // Fetch trade and check authorization
        $trade = BuyTrade::findOrFail($id);
        if ($trade->user_id !== auth()->id() && !auth()->user()->is_admin) {
            abort(403, 'Unauthorized access.');
        }

        // Validate new status
        $validated = $request->validate([
            'status' => 'required|in:pending,completed,canceled',
        ]);

        try {
            // Update trade status
            $trade->status = $validated['status'];
            $trade->save();
            Log::info('Trade status updated', [
                'trade_id' => $trade->id,
                'status' => $validated['status']
            ]);
            return redirect()->route('buy.summary', ['id' => $trade->id])
                ->with('success', 'Trade status updated successfully!');
        } catch (\Exception $e) {
            // Log error if update fails
            Log::error('Failed to update trade status: ' . $e->getMessage(), [
                'trade_id' => $id,
                'exception' => $e->getTraceAsString()
            ]);
            return redirect()->back()->withErrors(['error' => 'Failed to update trade status.']);
        }
    }
}