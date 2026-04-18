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
}
