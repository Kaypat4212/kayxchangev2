<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentGatewayService
{
    private string $sslCert = 'C:\xampp\php\extras\ssl\cacert.pem';

    // ──────────────────────────────────────────────────────────────
    // PAYSTACK
    // ──────────────────────────────────────────────────────────────

    public function initializePaystack(array $data): array
    {
        $secret = config('services.paystack.secret_key');

        try {
            $response = Http::withToken($secret)
                ->withOptions(['verify' => $this->sslCert, 'timeout' => 15])
                ->post('https://api.paystack.co/transaction/initialize', [
                    'email'        => $data['email'],
                    'amount'       => (int) ($data['amount'] * 100), // kobo
                    'reference'    => $data['reference'],
                    'callback_url' => $data['callback_url'],
                    'metadata'     => ['deposit_id' => $data['deposit_id']],
                ]);

            if ($response->successful() && $response->json('status') === true) {
                return [
                    'success'      => true,
                    'checkout_url' => $response->json('data.authorization_url'),
                    'reference'    => $response->json('data.reference'),
                ];
            }

            Log::error('Paystack init failed', ['body' => $response->json()]);
            return ['success' => false, 'message' => $response->json('message') ?? 'Paystack initialization failed.'];
        } catch (\Throwable $e) {
            Log::error('Paystack init exception: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Could not connect to Paystack. Please try again.'];
        }
    }

    public function verifyPaystack(string $reference): array
    {
        $secret = config('services.paystack.secret_key');

        try {
            $response = Http::withToken($secret)
                ->withOptions(['verify' => $this->sslCert, 'timeout' => 15])
                ->get("https://api.paystack.co/transaction/verify/{$reference}");

            if ($response->successful() && $response->json('data.status') === 'success') {
                return [
                    'success' => true,
                    'amount'  => $response->json('data.amount') / 100,
                    'data'    => $response->json('data'),
                ];
            }

            return ['success' => false, 'data' => $response->json()];
        } catch (\Throwable $e) {
            Log::error('Paystack verify exception: ' . $e->getMessage());
            return ['success' => false, 'data' => []];
        }
    }

    // ──────────────────────────────────────────────────────────────
    // KORAPAY
    // ──────────────────────────────────────────────────────────────

    public function initializeKorapay(array $data): array
    {
        $secret = config('services.korapay.secret_key');

        try {
            $response = Http::withToken($secret)
                ->withOptions(['verify' => $this->sslCert, 'timeout' => 15])
                ->post('https://api.korapay.com/merchant/api/v1/charges/initialize', [
                    'reference'    => $data['reference'],
                    'amount'       => $data['amount'],
                    'currency'     => 'NGN',
                    'redirect_url' => $data['callback_url'],
                    'customer'     => ['email' => $data['email'], 'name' => $data['name']],
                    'metadata'     => ['deposit_id' => $data['deposit_id']],
                ]);

            if ($response->successful() && $response->json('status') === true) {
                return [
                    'success'      => true,
                    'checkout_url' => $response->json('data.checkout_url'),
                    'reference'    => $response->json('data.reference') ?? $data['reference'],
                ];
            }

            Log::error('Korapay init failed', ['body' => $response->json()]);
            return ['success' => false, 'message' => $response->json('message') ?? 'Korapay initialization failed.'];
        } catch (\Throwable $e) {
            Log::error('Korapay init exception: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Could not connect to Korapay. Please try again.'];
        }
    }

    public function verifyKorapay(string $reference): array
    {
        $secret = config('services.korapay.secret_key');

        try {
            $response = Http::withToken($secret)
                ->withOptions(['verify' => $this->sslCert, 'timeout' => 15])
                ->get("https://api.korapay.com/merchant/api/v1/charges/{$reference}");

            if ($response->successful() && $response->json('data.status') === 'success') {
                return [
                    'success' => true,
                    'amount'  => $response->json('data.amount'),
                    'data'    => $response->json('data'),
                ];
            }

            return ['success' => false, 'data' => $response->json()];
        } catch (\Throwable $e) {
            Log::error('Korapay verify exception: ' . $e->getMessage());
            return ['success' => false, 'data' => []];
        }
    }

    // ──────────────────────────────────────────────────────────────
    // FLUTTERWAVE
    // ──────────────────────────────────────────────────────────────

    public function initializeFlutterwave(array $data): array
    {
        $secret = config('services.flutterwave.secret_key');

        try {
            $response = Http::withToken($secret)
                ->withOptions(['verify' => $this->sslCert, 'timeout' => 15])
                ->post('https://api.flutterwave.com/v3/payments', [
                    'tx_ref'         => $data['reference'],
                    'amount'         => $data['amount'],
                    'currency'       => 'NGN',
                    'redirect_url'   => $data['callback_url'],
                    'customer'       => [
                        'email' => $data['email'],
                        'name'  => $data['name'],
                    ],
                    'customizations' => [
                        'title'       => 'KayXchange Deposit',
                        'description' => 'Fund your KayXchange wallet',
                    ],
                    'meta' => ['deposit_id' => $data['deposit_id']],
                ]);

            if ($response->successful() && $response->json('status') === 'success') {
                return [
                    'success'      => true,
                    'checkout_url' => $response->json('data.link'),
                    'reference'    => $data['reference'],
                ];
            }

            Log::error('Flutterwave init failed', ['body' => $response->json()]);
            return ['success' => false, 'message' => $response->json('message') ?? 'Flutterwave initialization failed.'];
        } catch (\Throwable $e) {
            Log::error('Flutterwave init exception: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Could not connect to Flutterwave. Please try again.'];
        }
    }

    public function verifyFlutterwave(string $txRef): array
    {
        $secret = config('services.flutterwave.secret_key');

        try {
            $response = Http::withToken($secret)
                ->withOptions(['verify' => $this->sslCert, 'timeout' => 15])
                ->get('https://api.flutterwave.com/v3/transactions', ['tx_ref' => $txRef]);

            if ($response->successful() && $response->json('status') === 'success') {
                $transactions = $response->json('data') ?? [];
                foreach ($transactions as $tx) {
                    if ($tx['status'] === 'successful' && ($tx['currency'] ?? '') === 'NGN') {
                        return [
                            'success' => true,
                            'amount'  => $tx['amount'],
                            'data'    => $tx,
                        ];
                    }
                }
            }

            return ['success' => false, 'data' => $response->json()];
        } catch (\Throwable $e) {
            Log::error('Flutterwave verify exception: ' . $e->getMessage());
            return ['success' => false, 'data' => []];
        }
    }

    // ──────────────────────────────────────────────────────────────
    // PAYSTACK — Charge Authorization (auto-debit saved card)
    // ──────────────────────────────────────────────────────────────

    /**
     * Charge a previously authorized Paystack card without redirecting the user.
     * Requires a reusable authorization_code stored from an earlier payment.
     */
    public function chargePaystackAuthorization(array $data): array
    {
        // $data keys: email, amount (NGN), reference, authorization_code
        $secret = config('services.paystack.secret_key');

        try {
            $response = Http::withToken($secret)
                ->withOptions(['verify' => $this->sslCert, 'timeout' => 15])
                ->post('https://api.paystack.co/transaction/charge_authorization', [
                    'authorization_code' => $data['authorization_code'],
                    'email'              => $data['email'],
                    'amount'             => (int) ($data['amount'] * 100), // kobo
                    'reference'          => $data['reference'],
                    'metadata'           => ['deposit_id' => $data['deposit_id'] ?? null],
                ]);

            $status = $response->json('data.status');

            if ($response->successful() && in_array($status, ['success', 'pending'])) {
                return [
                    'success'   => true,
                    'status'    => $status,
                    'reference' => $response->json('data.reference'),
                    'data'      => $response->json('data'),
                ];
            }

            Log::error('Paystack charge_authorization failed', ['body' => $response->json()]);
            return ['success' => false, 'message' => $response->json('message') ?? 'Auto-debit failed.'];
        } catch (\Throwable $e) {
            Log::error('Paystack charge_authorization exception: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Could not connect to Paystack. Please try again.'];
        }
    }

    // ──────────────────────────────────────────────────────────────
    // OPAY — Web Cashier (redirect checkout + webhook)
    // ──────────────────────────────────────────────────────────────

    public function initializeOpay(array $data): array
    {
        $publicKey  = config('services.opay.public_key');
        $privateKey = config('services.opay.private_key');
        $merchantId = config('services.opay.merchant_id');
        $baseUrl    = config('services.opay.base_url', 'https://api.opayweb.com');

        $payload = [
            'reference'    => $data['reference'],
            'mchShortName' => 'KayXchange',
            'productName'  => 'Wallet Deposit',
            'productDesc'  => 'Fund your KayXchange wallet',
            'callbackUrl'  => $data['webhook_url'],
            'returnUrl'    => $data['callback_url'],
            'currency'     => 'NGN',
            'amount'       => (int) ($data['amount'] * 100), // kobo
            'expireAt'     => 3600,
            'userPhone'    => $data['phone'] ?? '',
        ];

        // OPay requires HMAC-SHA512 signature of the JSON payload with the private key
        $jsonPayload = json_encode($payload);
        $signature   = hash_hmac('sha512', $jsonPayload, $privateKey);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $publicKey,
                'MerchantId'    => $merchantId,
                'Sign'          => $signature,
                'Content-Type'  => 'application/json',
            ])
            ->withOptions(['verify' => $this->sslCert, 'timeout' => 15])
            ->post("{$baseUrl}/api/v3/cashier/checkout", $payload);

            if ($response->successful() && $response->json('code') === '00000') {
                return [
                    'success'      => true,
                    'checkout_url' => $response->json('data.cashierUrl'),
                    'reference'    => $response->json('data.reference') ?? $data['reference'],
                ];
            }

            Log::error('OPay init failed', ['body' => $response->json()]);
            return ['success' => false, 'message' => $response->json('message') ?? 'OPay initialization failed.'];
        } catch (\Throwable $e) {
            Log::error('OPay init exception: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Could not connect to OPay. Please try again.'];
        }
    }

    public function verifyOpay(string $reference): array
    {
        $publicKey  = config('services.opay.public_key');
        $privateKey = config('services.opay.private_key');
        $merchantId = config('services.opay.merchant_id');
        $baseUrl    = config('services.opay.base_url', 'https://api.opayweb.com');

        $payload   = json_encode(['reference' => $reference, 'orderNo' => '']);
        $signature = hash_hmac('sha512', $payload, $privateKey);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $publicKey,
                'MerchantId'    => $merchantId,
                'Sign'          => $signature,
                'Content-Type'  => 'application/json',
            ])
            ->withOptions(['verify' => $this->sslCert, 'timeout' => 15])
            ->post("{$baseUrl}/api/v3/cashier/query", ['reference' => $reference, 'orderNo' => '']);

            if ($response->successful() && $response->json('data.status') === 'SUCCESS') {
                return [
                    'success' => true,
                    'amount'  => $response->json('data.amount') / 100,
                    'data'    => $response->json('data'),
                ];
            }

            return ['success' => false, 'data' => $response->json()];
        } catch (\Throwable $e) {
            Log::error('OPay verify exception: ' . $e->getMessage());
            return ['success' => false, 'data' => []];
        }
    }

    /**
     * Verify that an incoming OPay webhook is genuine.
     * OPay signs the webhook body with HMAC-SHA512 using the merchant's private key.
     */
    public function verifyOpayWebhookSignature(string $rawBody, string $signature): bool
    {
        $privateKey = config('services.opay.private_key');
        if (empty($privateKey)) return false;
        return hash_equals(hash_hmac('sha512', $rawBody, $privateKey), $signature);
    }
}
