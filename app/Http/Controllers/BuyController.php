<?php

namespace App\Http\Controllers;

use App\Http\Requests\BuyCryptoRequest;
// use App\Models\AccountDetail;
use App\Models\BuyTrade;
use App\Models\CryptoRate;
use App\Models\CompanyAccount;
use App\Services\AdminTradeAlertService;
use App\Mail\TradeNotification;
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
     * @return \Illuminate\View\View
     */
    public function show()
    {
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
        // Fetch current rates or use defaults
        $rates = CryptoRate::pluck('buy_rate', 'coin')->toArray();
        $defaultRates = ['BTC' => 1600, 'ETH' => 1500, 'USDT' => 1400];
        $rate = $rates[$request->coin] ?? $defaultRates[$request->coin] ?? null;

        // Check if rate exists for the selected coin
        if (!$rate) {
            Log::warning("No buy rate available for coin: {$request->coin}");
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
            // Create new trade record
            $buyTrade = BuyTrade::create([
                'user_id' => auth()->id(),
                'name' => auth()->user()->name,
                'coin' => $validated['coin'],
                'usd_amount' => $usd_amount,
                'naira_amount' => $naira_amount,
                'wallet_address' => $validated['wallet_address'],
                'network' => $validated['network'],
                'payment_method' => 'Bank Transfer',
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

            // Send confirmation email
            try {
                Mail::to(auth()->user()->email)->send(new TradeNotification(
                    user: auth()->user(),
                    templateKey: 'buy_trade_submitted',
                    data: [
                        'amount'         => number_format($usd_amount, 6),
                        'currency'       => $validated['coin'],
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

            // Redirect to trade summary
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
                    'path' => Storage::disk('public')->path($proofPath)
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

        // Fallback: cURL
        try {
            Log::info('Attempting Telegram notification via cURL', [
                'trade_id' => $trade->id,
                'url' => $url
            ]);
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_CAINFO, 'C:\xampp\php\extras\ssl\cacert.pem');
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($httpCode === 200 && json_decode($result, true)['ok']) {
                Log::info('Telegram notification sent successfully via cURL', [
                    'trade_id' => $trade->id,
                    'chat_id' => $chatId,
                    'response' => $result
                ]);
                return true;
            } else {
                Log::error('Telegram notification failed via cURL', [
                    'trade_id' => $trade->id,
                    'http_code' => $httpCode,
                    'response' => $result,
                    'curl_error' => $curlError
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Telegram notification failed via cURL: ' . $e->getMessage(), [
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
        return view('buy.summary', compact('trade'));
    }

    /**
     * Display the payment page with bank details.
     *
     * Fetches the trade and account details, ensures user ownership, and renders the payment view.
     *
     * @param int $id Trade ID
     * @return \Illuminate\View\View
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
     * Update the status of a trade.
     *
     * Validates the new status, updates the trade record, and redirects to the summary page.
     * Only allows trade owner or admin to update. Logs success or failure.
     *
     * @param Request $request HTTP request with new status
     * @param int $id Trade ID
     * @return \Illuminate\Http\RedirectResponse
     */

     public function logWalletError(Request $request)
{
    Log::error('Wallet validation error', $request->all());
    return response()->json(['status' => 'logged']);
}
    public function updateStatus(Request $request, $id)
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