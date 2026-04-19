<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\CompanyAccount;
use App\Services\PaymentGatewayService;
use App\Mail\DepositApproved;
use App\Mail\TradeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class DepositController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->query('sort', 'created_at');
        $direction = $request->query('direction', 'desc');
        $validSorts = ['amount', 'status', 'transaction_ref', 'created_at'];
        $sort = in_array($sort, $validSorts) ? $sort : 'created_at';
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'desc';

        $deposits = Deposit::orderBy($sort, $direction)->paginate(10);

        Log::info('Deposits Loaded', [
            'count' => $deposits->count(),
            'total' => $deposits->total(),
            'current_page' => $deposits->currentPage(),
            'sort' => $sort,
            'direction' => $direction,
            'class' => get_class($deposits)
        ]);

        return view('deposits.index', compact('deposits'));
    }

    public function create()
    {
        $companyAccounts = CompanyAccount::all();
        $cryptoWallets = $this->getConfiguredCryptoWallets();
        Log::info('Company Accounts Loaded', ['count' => $companyAccounts->count()]);

        // A method is available only when BOTH the admin toggle is on AND API keys are present
        $sc = \App\Models\SiteContent::allKeyed();
        $enabledMethods = [
            // Bank Transfer never needs API keys
            'bank_transfer' => (bool) ($sc['pm_enabled_bank_transfer'] ?? true) && $companyAccounts->isNotEmpty(),
            // Manual crypto transfer (wallet addresses come from .env)
            'crypto_transfer' => (bool) ($sc['pm_enabled_crypto_transfer'] ?? true) && !empty($cryptoWallets),
            // Gateways: toggle must be on AND secret key must be non-empty
            'paystack'    => !empty(config('services.paystack.secret_key'))
                              && (bool) ($sc['pm_enabled_paystack'] ?? true),
            'korapay'     => !empty(config('services.korapay.secret_key'))
                              && (bool) ($sc['pm_enabled_korapay'] ?? true),
            'flutterwave' => !empty(config('services.flutterwave.secret_key'))
                              && (bool) ($sc['pm_enabled_flutterwave'] ?? true),
        ];

        return view('deposits.create', compact('companyAccounts', 'enabledMethods', 'cryptoWallets'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // AUTOMATIC PAYMENT GATEWAYS: initiate → redirect → callback → verify
    // ─────────────────────────────────────────────────────────────────────────

    public function initiate(Request $request)
    {
        // Only allow gateways that are both toggled on AND have API keys configured
        $sc = \App\Models\SiteContent::allKeyed();
        $allowedGateways = array_values(array_filter(
            ['paystack', 'korapay', 'flutterwave', 'opay'],
            fn($g) => !empty($sc['pm_enabled_' . $g]) && !empty(config('services.' . $g . '.secret_key') ?? config('services.' . $g . '.private_key'))
        ));

        $request->validate([
            'amount'         => 'required|numeric|min:1000',
            'payment_method' => ['required', \Illuminate\Validation\Rule::in($allowedGateways)],
        ]);

        $user = auth()->user();
        $ref  = 'DEP-' . Str::upper(Str::random(12));

        $deposit = Deposit::create([
            'user_id'           => $user->id,
            'amount'            => $request->amount,
            'currency'          => 'NGN',
            'payment_method'    => $request->payment_method,
            'transaction_ref'   => $ref,
            'gateway'           => $request->payment_method,
            'gateway_reference' => $ref,
            'status'            => 'pending',
        ]);

        $gateway     = new PaymentGatewayService();
        $callbackUrl = route('deposits.callback', ['gateway' => $request->payment_method]);

        $data = [
            'email'        => $user->email,
            'name'         => $user->name,
            'phone'        => $user->phone ?? '',
            'amount'       => $request->amount,
            'reference'    => $ref,
            'callback_url' => $callbackUrl,
            'webhook_url'  => route('deposits.webhook', ['gateway' => 'opay']),
            'deposit_id'   => $deposit->id,
        ];

        $result = match ($request->payment_method) {
            'paystack'    => $gateway->initializePaystack($data),
            'korapay'     => $gateway->initializeKorapay($data),
            'flutterwave' => $gateway->initializeFlutterwave($data),
            'opay'        => $gateway->initializeOpay($data),
        };

        if (! $result['success']) {
            $deposit->update(['status' => 'rejected']);
            Log::error('Gateway init failed', ['method' => $request->payment_method, 'result' => $result]);
            return redirect()->back()
                ->withErrors(['error' => $result['message'] ?? 'Payment initialization failed. Please try again.'])
                ->withInput();
        }

        Log::info('Gateway payment initiated', [
            'deposit_id' => $deposit->id,
            'gateway'    => $request->payment_method,
            'reference'  => $ref,
        ]);

        // Send deposit initiated email
        try {
            Mail::to($user->email)->send(new TradeNotification(
                user: $user,
                templateKey: 'deposit_initiated',
                data: [
                    'amount'         => number_format((float)$request->amount, 2),
                    'payment_method' => ucfirst($request->payment_method),
                    'reference'      => $ref,
                ],
                badge: ['text' => 'Deposit Initiated', 'color' => '#0d6efd'],
                ctaUrl: url('/dashboard'),
                ctaText: 'Go to Dashboard',
            ));
        } catch (\Exception $mailEx) {
            Log::warning('Deposit initiated email failed: ' . $mailEx->getMessage());
        }

        return redirect()->away($result['checkout_url']);
    }

    public function callback(Request $request)
    {
        $gateway   = $request->query('gateway');
        $reference = $request->query('reference')
            ?? $request->query('trxref')
            ?? $request->query('tx_ref')
            ?? $request->query('transaction_id');

        if (! $gateway || ! $reference) {
            return redirect()->route('deposits.index')
                ->withErrors(['error' => 'Invalid payment callback. Please contact support.']);
        }

        $deposit = Deposit::where('gateway_reference', $reference)
            ->orWhere('transaction_ref', $reference)
            ->first();

        if (! $deposit) {
            Log::error('Callback: deposit not found', ['gateway' => $gateway, 'reference' => $reference]);
            return redirect()->route('deposits.index')
                ->withErrors(['error' => 'Deposit record not found. Please contact support.']);
        }

        if ($deposit->status === 'approved') {
            return redirect()->route('deposits.index')
                ->with('success', 'Your deposit has already been credited!');
        }

        $gatewayService = new PaymentGatewayService();

        // If Paystack, also verify with API and credit
        $verification = match ($gateway) {
            'paystack'    => $gatewayService->verifyPaystack($reference),
            'korapay'     => $gatewayService->verifyKorapay($reference),
            'flutterwave' => $gatewayService->verifyFlutterwave($reference),
            'opay'        => $gatewayService->verifyOpay($reference),
            default       => ['success' => false, 'data' => []],
        };

        if ($verification['success']) {
            $deposit->update([
                'status'           => 'approved',
                'gateway_response' => json_encode($verification['data'] ?? []),
            ]);

            $deposit->user->wallet()->increment('balance', $deposit->amount);

            Log::info('Gateway deposit approved', [
                'deposit_id' => $deposit->id,
                'gateway'    => $gateway,
                'amount'     => $deposit->amount,
                'user_id'    => $deposit->user_id,
            ]);

            $this->sendTelegramAlert($deposit, false);

            return redirect()->route('deposits.index')
                ->with('success', 'Payment successful! ₦' . number_format($deposit->amount, 2) . ' has been credited to your wallet.');
        }

        $deposit->update([
            'status'           => 'rejected',
            'gateway_response' => json_encode($verification['data'] ?? []),
        ]);

        Log::warning('Gateway payment verification failed', [
            'deposit_id' => $deposit->id,
            'gateway'    => $gateway,
            'reference'  => $reference,
        ]);

        return redirect()->route('deposits.index')
            ->withErrors(['error' => 'Payment could not be verified. If you were charged, please contact support with reference: ' . $reference]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // WEBHOOKS — server-to-server notifications (no CSRF / no auth)
    // ─────────────────────────────────────────────────────────────────────────

    public function webhook(Request $request, string $gateway)
    {
        match ($gateway) {
            'paystack'    => $this->handlePaystackWebhook($request),
            'korapay'     => $this->handleKorapayWebhook($request),
            'flutterwave' => $this->handleFlutterwaveWebhook($request),
            'opay'        => $this->handleOpayWebhook($request),
            default       => null,
        };

        return response('OK', 200);
    }

    private function handlePaystackWebhook(Request $request): void
    {
        $hash = hash_hmac('sha512', $request->getContent(), config('services.paystack.secret_key'));
        if ($hash !== $request->header('x-paystack-signature')) {
            Log::warning('Paystack webhook: invalid signature');
            return;
        }
        if ($request->json('event') !== 'charge.success') return;

        $reference = $request->json('data.reference');

        // Store reusable card authorization on the user so they can be auto-debited later
        $authorization = $request->json('data.authorization');
        if ($authorization && ($authorization['reusable'] ?? false)) {
            $deposit = Deposit::where('gateway_reference', $reference)
                ->orWhere('transaction_ref', $reference)
                ->first();
            if ($deposit && $deposit->user) {
                $deposit->user->update([
                    'paystack_auth_code'      => $authorization['authorization_code'],
                    'paystack_auth_email'     => $request->json('data.customer.email'),
                    'paystack_auth_card_last4'=> $authorization['last4'] ?? null,
                    'paystack_auth_card_type' => $authorization['card_type'] ?? null,
                ]);
                Log::info('Paystack authorization saved for user #' . $deposit->user_id);
            }
        }

        $this->approveDepositByRef($reference, $request->getContent());
    }

    private function handleKorapayWebhook(Request $request): void
    {
        $hash = hash_hmac('sha256', $request->getContent(), config('services.korapay.secret_key'));
        if ($hash !== $request->header('x-korapay-signature')) {
            Log::warning('Korapay webhook: invalid signature');
            return;
        }
        if ($request->json('event') !== 'charge.success') return;
        $this->approveDepositByRef($request->json('data.reference'), $request->getContent());
    }

    private function handleFlutterwaveWebhook(Request $request): void
    {
        $secretHash = config('services.flutterwave.webhook_hash');
        if ($secretHash && $request->header('verif-hash') !== $secretHash) {
            Log::warning('Flutterwave webhook: invalid hash');
            return;
        }
        if ($request->json('event') !== 'charge.completed') return;
        if ($request->json('data.status') !== 'successful') return;
        $this->approveDepositByRef($request->json('data.tx_ref'), $request->getContent());
    }

    private function handleOpayWebhook(Request $request): void
    {
        $gatewayService = new PaymentGatewayService();
        $signature = $request->header('Sign') ?? '';
        if (! $gatewayService->verifyOpayWebhookSignature($request->getContent(), $signature)) {
            Log::warning('OPay webhook: invalid signature');
            return;
        }
        if ($request->json('status') !== 'SUCCESS') return;
        $this->approveDepositByRef($request->json('reference'), $request->getContent());
    }

    private function approveDepositByRef(string $reference, string $rawPayload): void
    {
        $deposit = Deposit::where('gateway_reference', $reference)
            ->orWhere('transaction_ref', $reference)
            ->first();

        if (! $deposit || $deposit->status === 'approved') return;

        $deposit->update([
            'status'           => 'approved',
            'gateway_response' => $rawPayload,
        ]);

        $deposit->user->wallet()->increment('balance', $deposit->amount);

        Log::info('Deposit approved via webhook', [
            'deposit_id' => $deposit->id,
            'reference'  => $reference,
            'amount'     => $deposit->amount,
        ]);

        // Send approval email
        try {
            Mail::to($deposit->user->email)->send(new DepositApproved($deposit));
        } catch (\Exception $e) {
            Log::warning('Webhook deposit approval email failed: ' . $e->getMessage());
        }

        // Send Telegram alert to admin
        $token  = config('services.telegram.token');
        $chatId = config('services.telegram.chat_id');
        if ($token && $chatId) {
            $message = "✅ *Deposit Auto-Approved (Webhook)*\n\n"
                . "👤 User: {$deposit->user->name} ({$deposit->user->email})\n"
                . "💰 Amount: ₦" . number_format($deposit->amount, 2) . "\n"
                . "🏦 Gateway: " . ucfirst($deposit->gateway ?? $deposit->payment_method) . "\n"
                . "🔖 Reference: {$reference}";
            try {
                Http::withOptions(['verify' => 'C:\\xampp\\php\\extras\\ssl\\cacert.pem', 'timeout' => 10])
                    ->post("https://api.telegram.org/bot{$token}/sendMessage", [
                        'chat_id'    => $chatId,
                        'text'       => $message,
                        'parse_mode' => 'Markdown',
                    ]);
            } catch (\Exception $e) {
                Log::warning('Webhook Telegram alert failed: ' . $e->getMessage());
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PAYSTACK AUTO-DEBIT — charge a saved card without redirect
    // ─────────────────────────────────────────────────────────────────────────

    public function chargeAuthorization(Request $request)
    {
        $user = auth()->user();

        if (empty($user->paystack_auth_code)) {
            return redirect()->back()
                ->withErrors(['error' => 'No saved card found. Please make a deposit with Paystack first to save your card.']);
        }

        $request->validate([
            'amount' => 'required|numeric|min:1000',
        ]);

        $ref = 'AUTO-' . Str::upper(Str::random(12));

        $deposit = Deposit::create([
            'user_id'           => $user->id,
            'amount'            => $request->amount,
            'currency'          => 'NGN',
            'payment_method'    => 'paystack',
            'transaction_ref'   => $ref,
            'gateway'           => 'paystack',
            'gateway_reference' => $ref,
            'status'            => 'pending',
        ]);

        $gateway = new PaymentGatewayService();
        $result  = $gateway->chargePaystackAuthorization([
            'authorization_code' => $user->paystack_auth_code,
            'email'              => $user->paystack_auth_email ?? $user->email,
            'amount'             => $request->amount,
            'reference'          => $ref,
            'deposit_id'         => $deposit->id,
        ]);

        if (! $result['success']) {
            $deposit->update(['status' => 'rejected']);
            return redirect()->back()
                ->withErrors(['error' => $result['message'] ?? 'Auto-debit failed. Please try the regular deposit flow.']);
        }

        // If instantly successful, approve right away
        if (($result['status'] ?? '') === 'success') {
            $this->approveDepositByRef($ref, json_encode($result['data'] ?? []));
            return redirect()->route('deposits.index')
                ->with('success', '₦' . number_format($request->amount, 2) . ' auto-debited and credited to your wallet!');
        }

        // Pending — will be confirmed via webhook
        Log::info('Paystack auto-debit pending', ['deposit_id' => $deposit->id, 'reference' => $ref]);
        return redirect()->route('deposits.index')
            ->with('success', 'Auto-debit initiated. Your wallet will be credited once confirmed.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // BANK TRANSFER (manual flow — user uploads proof for admin approval)
    // ─────────────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $walletLookup = collect($this->getConfiguredCryptoWallets())->keyBy('key');

        $request->validate([
            'amount'             => 'required|numeric|min:1000',
            'payment_method'     => 'required|in:bank_transfer,crypto_transfer',
            'company_account_id' => 'required_if:payment_method,bank_transfer|nullable|exists:company_accounts,id',
            'crypto_wallet_key'  => 'required_if:payment_method,crypto_transfer|nullable|string',
            'proof_of_payment'   => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        if ($request->payment_method === 'crypto_transfer' && !$walletLookup->has($request->crypto_wallet_key)) {
            return redirect()->back()
                ->withErrors(['crypto_wallet_key' => 'Selected wallet is invalid. Please refresh and try again.'])
                ->withInput();
        }

        try {
            $deposit = new Deposit();
            $deposit->amount = $request->amount;
            $deposit->status = 'pending';
            $deposit->transaction_ref = 'DEP-' . Str::upper(Str::random(10));
            $deposit->company_account_id = $request->payment_method === 'bank_transfer' ? $request->company_account_id : null;
            $deposit->user_id = auth()->id();
            $deposit->payment_method = $request->payment_method;

            if ($request->payment_method === 'crypto_transfer') {
                $wallet = $walletLookup->get($request->crypto_wallet_key);
                $deposit->gateway_response = json_encode([
                    'crypto_wallet_key' => $wallet['key'],
                    'crypto_wallet_name' => $wallet['name'],
                    'crypto_wallet_network' => $wallet['network'],
                    'crypto_wallet_address' => $wallet['address'],
                ]);
            }

            // Store proof of payment
            if ($request->hasFile('proof_of_payment') && $request->file('proof_of_payment')->isValid()) {
                $path = $request->file('proof_of_payment')->store('proofs', 'public');
                $deposit->proof_of_payment = $path;
                // Also set the 'proof' column for backward compatibility
                $deposit->proof = $path;
            }

            $deposit->save();

            // Send Telegram notification
            $this->sendTelegramAlert($deposit, true);

            return redirect()->route('deposits.index')->with('success', 'Deposit submitted successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to process deposit', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->withErrors(['error' => 'Failed to submit deposit. Please try again.']);
        }
    }

    private function getConfiguredCryptoWallets(): array
    {
        $walletMap = config('wallets', []);

        $labelMap = [
            'BTC' => ['name' => 'Bitcoin', 'network' => 'BTC'],
            'ETH' => ['name' => 'Ethereum', 'network' => 'ERC20'],
            'SOL' => ['name' => 'Solana', 'network' => 'SOL'],
            'USDT' => ['name' => 'USDT', 'network' => 'TRC20'],
            'USDT_ERC20' => ['name' => 'USDT (ERC20)', 'network' => 'ERC20'],
            'USDT_TRC20' => ['name' => 'USDT (TRC20)', 'network' => 'TRC20'],
            'USDT_BEP20' => ['name' => 'USDT (BEP20)', 'network' => 'BEP20'],
        ];

        $wallets = [];
        foreach ($labelMap as $key => $meta) {
            $address = trim((string) ($walletMap[$key] ?? ''));
            if ($address === '') {
                continue;
            }

            $wallets[] = [
                'key' => $key,
                'name' => $meta['name'],
                'network' => $meta['network'],
                'address' => $address,
            ];
        }

        return $wallets;
    }

    public function updateStatus(Request $request, Deposit $deposit)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'admin_note' => 'nullable|string|max:255',
        ]);

        try {
            $deposit->status = $request->status;
            $deposit->admin_note = $request->admin_note;
            $deposit->save();

            // Send email notification if approved
            if ($request->status === 'approved' && $deposit->user && $deposit->user->email) {
                Mail::to($deposit->user->email)->send(new DepositApproved($deposit));
                Log::info('Deposit approval email sent', [
                    'deposit_id' => $deposit->id,
                    'user_email' => $deposit->user->email
                ]);
            }

            return redirect()->route('deposits.index')->with('success', 'Deposit status updated successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to update deposit status', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->withErrors(['error' => 'Failed to update deposit status. Please try again.']);
        }
    }

    protected function sendTelegramAlert(Deposit $deposit, $includeProof = false)
    {
        $user = auth()->user();
        $token = config('services.telegram.token');
        $chatId = config('services.telegram.chat_id');

        Log::info('Attempting Telegram notification', [
            'deposit_id' => $deposit->id,
            'user_id' => $user->id,
            'name' => $user->name,
            'token_set' => !empty($token) ? 'yes' : 'no',
            'chat_id_set' => !empty($chatId) ? 'yes' : 'no'
        ]);

        if (empty($token) || empty($chatId)) {
            Log::error('Telegram notification aborted: Missing token or chat_id', [
                'deposit_id' => $deposit->id,
                'user_id' => $user->id
            ]);
            return false;
        }

        $companyAccount = CompanyAccount::find($deposit->company_account_id);
        $message = "📥 *New Deposit Alert!*\n\n"
            . "*User:* {$user->name} ({$user->email})\n"
            . "*User ID:* {$user->id}\n"
            . "*Transaction Ref:* {$deposit->transaction_ref}\n"
            . "*Amount:* ₦" . number_format($deposit->amount, 2) . "\n"
            . "*Payment Method:* " . ucfirst(str_replace('_', ' ', $deposit->payment_method ?? 'bank_transfer')) . "\n"
            . "*Company Account:* " . ($companyAccount ? "{$companyAccount->account_name} ({$companyAccount->bank_name} - {$companyAccount->account_number})" : 'N/A') . "\n"
            . "*Status:* {$deposit->status}\n"
            . "*Submitted At:* " . $deposit->created_at->format('M d, Y H:i');

        if ($includeProof && $deposit->proof_of_payment) {
            $proofPath = $deposit->proof_of_payment;
            $proofUrl = asset('storage/' . $proofPath);
            if (Storage::disk('public')->exists($proofPath)) {
                $message .= "\n🧾 *Proof of Payment:* [View Image]({$proofUrl})";
                Log::info('Proof of payment URL included', [
                    'deposit_id' => $deposit->id,
                    'url' => $proofUrl,
                    'path' => $proofPath
                ]);
            } else {
                Log::warning('Proof of payment file not found', [
                    'deposit_id' => $deposit->id,
                    'path' => $proofPath
                ]);
                $message .= "\n🧾 *Proof of Payment:* (File not found)";
            }
        }

        $url = "https://api.telegram.org/bot{$token}/sendMessage";
        $payload = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown',
            'disable_web_page_preview' => false,
        ];

        Log::debug('Telegram payload', [
            'deposit_id' => $deposit->id,
            'url' => $url,
            'payload' => $payload
        ]);

        // Primary method: HTTP client
        try {
            $response = Http::withOptions([
                'verify' => 'C:\xampp\php\extras\ssl\cacert.pem',
                'timeout' => 15,
            ])->retry(3, 1000)->post($url, $payload);
            if ($response->successful()) {
                Log::info('Telegram notification sent successfully via Http', [
                    'deposit_id' => $deposit->id,
                    'chat_id' => $chatId,
                    'response' => $response->json()
                ]);
                return true;
            } else {
                Log::error('Telegram notification failed via Http', [
                    'deposit_id' => $deposit->id,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Telegram notification failed via Http: ' . $e->getMessage(), [
                'deposit_id' => $deposit->id,
                'exception' => $e->getTraceAsString()
            ]);
        }

        // Fallback: cURL
        try {
            Log::info('Attempting Telegram notification via cURL', [
                'deposit_id' => $deposit->id,
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
                    'deposit_id' => $deposit->id,
                    'chat_id' => $chatId,
                    'response' => $result
                ]);
                return true;
            } else {
                Log::error('Telegram notification failed via cURL', [
                    'deposit_id' => $deposit->id,
                    'http_code' => $httpCode,
                    'response' => $result,
                    'curl_error' => $curlError
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Telegram notification failed via cURL: ' . $e->getMessage(), [
                'deposit_id' => $deposit->id,
                'exception' => $e->getTraceAsString()
            ]);
        }

        return false;
    }
}