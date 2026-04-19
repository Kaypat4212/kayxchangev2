<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayoutService
{
    // ──────────────────────────────────────────────
    // PAYSTACK TRANSFER PAYOUT
    // ──────────────────────────────────────────────

    /**
     * Create a Paystack transfer recipient for a bank account.
     * Returns ['success' => true, 'recipient_code' => '...'] or ['success' => false, 'error' => '...']
     */
    public function createPaystackRecipient(array $bankDetails): array
    {
        $secret = config('services.paystack.secret_key');

        $payload = [
            'type'           => 'nuban',
            'name'           => $bankDetails['account_name'],
            'account_number' => $bankDetails['account_number'],
            'bank_code'      => $bankDetails['bank_code'] ?? '',
            'currency'       => 'NGN',
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$secret}",
                'Content-Type'  => 'application/json',
            ])->timeout(30)->post('https://api.paystack.co/transferrecipient', $payload);

            $body = $response->json();
            Log::info('[PayoutService] Paystack create recipient response', ['status' => $response->status(), 'body' => $body]);

            if ($response->successful() && isset($body['data']['recipient_code'])) {
                return ['success' => true, 'recipient_code' => $body['data']['recipient_code']];
            }

            return ['success' => false, 'error' => $body['message'] ?? 'Failed to create recipient'];
        } catch (\Throwable $e) {
            Log::error('[PayoutService] Paystack create recipient exception: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Initiate a Paystack transfer (actual payout).
     * Returns ['success' => true, 'reference' => '...', 'transfer_code' => '...'] or error.
     */
    public function initiatePaystackTransfer(string $recipientCode, float $amount, string $reference, string $reason = 'Withdrawal'): array
    {
        $secret = config('services.paystack.secret_key');

        $payload = [
            'source'    => 'balance',
            'amount'    => (int) round($amount * 100), // kobo
            'recipient' => $recipientCode,
            'reason'    => $reason,
            'reference' => $reference,
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$secret}",
                'Content-Type'  => 'application/json',
            ])->timeout(30)->post('https://api.paystack.co/transfer', $payload);

            $body = $response->json();
            Log::info('[PayoutService] Paystack initiate transfer response', ['status' => $response->status(), 'body' => $body]);

            if ($response->successful() && isset($body['data']['transfer_code'])) {
                return [
                    'success'       => true,
                    'reference'     => $body['data']['reference'] ?? $reference,
                    'transfer_code' => $body['data']['transfer_code'],
                    'payout_status' => $body['data']['status'] ?? 'pending',
                ];
            }

            return ['success' => false, 'error' => $body['message'] ?? 'Transfer initiation failed'];
        } catch (\Throwable $e) {
            Log::error('[PayoutService] Paystack initiate transfer exception: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Full Paystack payout flow: create recipient then initiate transfer.
     */
    public function payoutViaPaystack(array $bankDetails, float $amount, string $reference): array
    {
        // Step 1: Create recipient
        $recipient = $this->createPaystackRecipient($bankDetails);
        if (!$recipient['success']) {
            return $recipient;
        }

        // Step 2: Initiate transfer
        $transfer = $this->initiatePaystackTransfer(
            $recipient['recipient_code'],
            $amount,
            $reference,
            'KayXchange Withdrawal'
        );

        if (!$transfer['success']) {
            return $transfer;
        }

        return array_merge($transfer, ['recipient_code' => $recipient['recipient_code']]);
    }

    /**
     * Verify a Paystack transfer status.
     */
    public function verifyPaystackTransfer(string $reference): array
    {
        $secret = config('services.paystack.secret_key');

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$secret}",
            ])->timeout(20)->get("https://api.paystack.co/transfer/{$reference}");

            $body = $response->json();

            if ($response->successful() && isset($body['data']['status'])) {
                $status = $body['data']['status']; // 'success'|'failed'|'pending'|'reversed'
                return ['success' => true, 'status' => $status, 'data' => $body['data']];
            }

            return ['success' => false, 'error' => $body['message'] ?? 'Verification failed'];
        } catch (\Throwable $e) {
            Log::error('[PayoutService] Paystack verify transfer exception: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Handle incoming Paystack Transfer webhook event.
     * Returns ['status' => 'success'|'failed'|'reversed', 'reference' => '...']
     */
    public function parsePaystackTransferWebhook(array $payload): array
    {
        $event = $payload['event'] ?? '';
        $data  = $payload['data'] ?? [];

        $status = match ($event) {
            'transfer.success'  => 'success',
            'transfer.failed'   => 'failed',
            'transfer.reversed' => 'reversed',
            default             => null,
        };

        if (!$status) {
            return ['handled' => false];
        }

        return [
            'handled'       => true,
            'status'        => $status,
            'reference'     => $data['reference'] ?? '',
            'transfer_code' => $data['transfer_code'] ?? '',
            'amount'        => isset($data['amount']) ? $data['amount'] / 100 : null,
        ];
    }

    // ──────────────────────────────────────────────
    // OPAY PAYOUT / DISBURSEMENT
    // ──────────────────────────────────────────────

    /**
     * Initiate an OPay bank transfer payout.
     */
    public function payoutViaOpay(array $bankDetails, float $amount, string $reference, string $userName): array
    {
        $privateKey  = config('services.opay.private_key');
        $merchantId  = config('services.opay.merchant_id');
        $baseUrl     = rtrim(config('services.opay.base_url', 'https://api.opayweb.com'), '/');

        if (!$privateKey || !$merchantId) {
            return ['success' => false, 'error' => 'OPay credentials not configured'];
        }

        $payload = [
            'amount'          => [
                'currency' => 'NGN',
                'total'    => (int) round($amount * 100), // kobo
            ],
            'country'         => 'NG',
            'reference'       => $reference,
            'reason'          => 'KayXchange Withdrawal',
            'receiver'        => [
                'bankAccountNumber' => $bankDetails['account_number'],
                'bankCode'          => $bankDetails['bank_code'] ?? '',
                'name'              => $bankDetails['account_name'],
                'type'              => 'bank_account',
            ],
        ];

        $body      = json_encode($payload);
        $timestamp = (string) round(microtime(true) * 1000);
        $signature = hash_hmac('sha512', $body, $privateKey);

        try {
            $response = Http::withHeaders([
                'Authorization'  => "Bearer {$privateKey}",
                'MerchantId'     => $merchantId,
                'OPay-Timestamp' => $timestamp,
                'OPay-Signature' => $signature,
                'Content-Type'   => 'application/json',
            ])->timeout(30)->post("{$baseUrl}/api/v3/transfer/toBank", $payload);

            $res = $response->json();
            Log::info('[PayoutService] OPay payout response', ['status' => $response->status(), 'body' => $res]);

            $code = $res['code'] ?? null;
            if ($response->successful() && in_array($code, ['00000', '20000'])) {
                return [
                    'success'       => true,
                    'reference'     => $res['data']['reference'] ?? $reference,
                    'payout_status' => strtolower($res['data']['status'] ?? 'pending'),
                ];
            }

            return ['success' => false, 'error' => $res['message'] ?? 'OPay payout failed'];
        } catch (\Throwable $e) {
            Log::error('[PayoutService] OPay payout exception: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Verify an OPay transfer status.
     */
    public function verifyOpayTransfer(string $reference): array
    {
        $privateKey = config('services.opay.private_key');
        $merchantId = config('services.opay.merchant_id');
        $baseUrl    = rtrim(config('services.opay.base_url', 'https://api.opayweb.com'), '/');

        $payload   = ['reference' => $reference, 'country' => 'NG'];
        $body      = json_encode($payload);
        $signature = hash_hmac('sha512', $body, $privateKey);
        $timestamp = (string) round(microtime(true) * 1000);

        try {
            $response = Http::withHeaders([
                'Authorization'  => "Bearer {$privateKey}",
                'MerchantId'     => $merchantId,
                'OPay-Timestamp' => $timestamp,
                'OPay-Signature' => $signature,
                'Content-Type'   => 'application/json',
            ])->timeout(20)->post("{$baseUrl}/api/v3/transfer/status", $payload);

            $res = $response->json();

            if ($response->successful() && isset($res['data']['status'])) {
                return ['success' => true, 'status' => strtolower($res['data']['status']), 'data' => $res['data']];
            }

            return ['success' => false, 'error' => $res['message'] ?? 'OPay verification failed'];
        } catch (\Throwable $e) {
            Log::error('[PayoutService] OPay verify transfer exception: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Verify an OPay payout webhook HMAC signature.
     */
    public function verifyOpayPayoutWebhookSignature(string $rawBody, string $signature): bool
    {
        $privateKey = config('services.opay.private_key');
        if (!$privateKey) {
            return false;
        }
        $expected = hash_hmac('sha512', $rawBody, $privateKey);
        return hash_equals($expected, strtolower($signature));
    }
}
