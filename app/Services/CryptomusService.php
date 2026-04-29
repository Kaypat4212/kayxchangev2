<?php

namespace App\Services;

use App\Models\AdminSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CryptomusService
{
    private string $apiKey;
    private string $merchantId;
    private string $baseUrl = 'https://api.cryptomus.com/v1/';

    public function __construct()
    {
        $this->apiKey = AdminSetting::get('cryptomus_api_key', '');
        $this->merchantId = AdminSetting::get('cryptomus_merchant_id', '');
    }

    /**
     * Check if Cryptomus is enabled and configured
     */
    public function isEnabled(): bool
    {
        return AdminSetting::get('cryptomus_enabled', '0') === '1' &&
               !empty($this->apiKey) &&
               !empty($this->merchantId);
    }

    /**
     * Create a payment invoice
     */
    public function createPayment(array $data): array
    {
        if (!$this->isEnabled()) {
            throw new \Exception('Cryptomus is not enabled or configured');
        }

        $payload = [
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'USD',
            'crypto_currency' => $data['crypto_currency'] ?? 'USDT',
            'order_id' => $data['order_id'],
            'url_callback' => $data['url_callback'] ?? null,
            'url_return' => $data['url_return'] ?? null,
            'lifetime' => $data['lifetime'] ?? 3600, // 1 hour
            'additional_data' => $data['additional_data'] ?? null,
        ];

        try {
            $response = Http::withHeaders([
                'merchant' => $this->merchantId,
                'sign' => $this->generateSign($payload),
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . 'payment', $payload);

            if ($response->successful()) {
                $result = $response->json();
                Log::info('Cryptomus payment created', [
                    'order_id' => $data['order_id'],
                    'payment_id' => $result['result']['uuid'] ?? null
                ]);
                return $result;
            }

            Log::error('Cryptomus payment creation failed', [
                'order_id' => $data['order_id'],
                'response' => $response->body()
            ]);
            throw new \Exception('Failed to create Cryptomus payment: ' . $response->body());

        } catch (\Exception $e) {
            Log::error('Cryptomus API error', [
                'order_id' => $data['order_id'],
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Check payment status
     */
    public function checkPayment(string $paymentId): array
    {
        if (!$this->isEnabled()) {
            throw new \Exception('Cryptomus is not enabled or configured');
        }

        $payload = [
            'uuid' => $paymentId
        ];

        try {
            $response = Http::withHeaders([
                'merchant' => $this->merchantId,
                'sign' => $this->generateSign($payload),
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . 'payment/info', $payload);

            if ($response->successful()) {
                return $response->json();
            }

            throw new \Exception('Failed to check payment status: ' . $response->body());

        } catch (\Exception $e) {
            Log::error('Cryptomus payment check error', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get supported cryptocurrencies
     */
    public function getCurrencies(): array
    {
        if (!$this->isEnabled()) {
            return [];
        }

        try {
            $response = Http::withHeaders([
                'merchant' => $this->merchantId,
                'sign' => $this->generateSign([]),
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . 'currencies');

            if ($response->successful()) {
                return $response->json()['result'] ?? [];
            }

            return [];

        } catch (\Exception $e) {
            Log::error('Cryptomus currencies fetch error', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Generate signature for API requests
     */
    private function generateSign(array $data): string
    {
        $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return md5(base64_encode($jsonData) . $this->apiKey);
    }

    /**
     * Validate webhook signature
     */
    public function validateWebhook(array $data, string $sign): bool
    {
        $expectedSign = md5(base64_encode(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) . $this->apiKey);
        return hash_equals($expectedSign, $sign);
    }

    /**
     * Convert fiat to crypto amount
     */
    public function convertToCrypto(float $fiatAmount, string $fiatCurrency, string $cryptoCurrency): float
    {
        // This is a simplified conversion - in production you'd use real exchange rates
        // For now, we'll use approximate rates
        $rates = [
            'BTC' => ['USD' => 45000, 'NGN' => 45000 * 1600], // Approximate BTC rate
            'ETH' => ['USD' => 2500, 'NGN' => 2500 * 1600],   // Approximate ETH rate
            'USDT' => ['USD' => 1, 'NGN' => 1600],            // Stable coin
            'USDC' => ['USD' => 1, 'NGN' => 1600],            // Stable coin
        ];

        if (!isset($rates[$cryptoCurrency][$fiatCurrency])) {
            throw new \Exception("Conversion rate not available for {$cryptoCurrency}/{$fiatCurrency}");
        }

        return $fiatAmount / $rates[$cryptoCurrency][$fiatCurrency];
    }
}