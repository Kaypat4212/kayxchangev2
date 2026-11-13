<?php

namespace App\Services;

use App\Exceptions\NowPaymentsApiException;
use App\Models\AdminSetting;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;

class NowPaymentsService
{
    private string $apiKey;
    private string $ipnSecret;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = trim((string) AdminSetting::getSetting('nowpayments_api_key', ''));
        $this->ipnSecret = trim((string) AdminSetting::getSetting('nowpayments_ipn_secret', ''));
        $sandbox = AdminSetting::getSetting('nowpayments_sandbox', '1') !== '0';
        $this->baseUrl = $sandbox ? 'https://api-sandbox.nowpayments.io/v1' : 'https://api.nowpayments.io/v1';
    }

    public function isEnabled(): bool
    {
        return AdminSetting::getSetting('nowpayments_enabled', '0') === '1' && $this->isConfigured();
    }

    public function isConfigured(): bool { return $this->apiKey !== '' && $this->ipnSecret !== ''; }

    public function environment(): string
    {
        return str_contains($this->baseUrl, 'api-sandbox') ? 'sandbox' : 'live';
    }

    /** Validate the saved API key against the currently selected environment. */
    public function testConnection(): array
    {
        if ($this->apiKey === '') throw new \RuntimeException('Enter and save a NOWPayments API key first.');
        $response = Http::acceptJson()->withHeaders(['x-api-key' => $this->apiKey])
            ->timeout(12)->get($this->baseUrl . '/currencies');
        if (!$response->successful()) $this->throwProviderError($response, 'validate the API key');

        $currencies = $response->json('currencies', []);
        return ['environment'=>$this->environment(), 'currency_count'=>is_array($currencies) ? count($currencies) : 0];
    }

    public function minimumUsd(string $payCurrency): float
    {
        if ($this->apiKey === '') throw new \RuntimeException('Enter and save a NOWPayments API key first.');
        try {
            $response = Http::acceptJson()->withHeaders(['x-api-key'=>$this->apiKey])->timeout(10)
                ->get($this->baseUrl . '/min-amount', [
                    'currency_from'=>'usd', 'currency_to'=>strtolower($payCurrency), 'fiat_equivalent'=>'usd',
                    'is_fixed_rate'=>AdminSetting::getSetting('nowpayments_fixed_rate', '0') === '1' ? 'true' : 'false',
                    'is_fee_paid_by_user'=>AdminSetting::getSetting('nowpayments_fee_paid_by_user', '0') === '1' ? 'true' : 'false',
                ]);
        } catch (ConnectionException $e) {
            throw new NowPaymentsApiException('NOWPayments is temporarily unreachable.', 0, $e);
        }
        if (!$response->successful()) $this->throwProviderError($response, 'check the minimum payment amount');
        $fiatMinimum = $response->json('fiat_equivalent');
        if (is_numeric($fiatMinimum)) return max(0, (float)$fiatMinimum);
        return 0.0;
    }

    /** Create a direct payment so the customer can pay the provider's generated address. */
    public function createPayment(float $usdAmount, string $currencyCode, string $orderId, string $callbackUrl, string $description): array
    {
        if (!$this->isEnabled()) throw new \RuntimeException('NOWPayments is not enabled or fully configured.');
        $payload = [
            'price_amount' => round($usdAmount, 2),
            'price_currency' => 'usd',
            'pay_currency' => strtolower($currencyCode),
            'ipn_callback_url' => $callbackUrl,
            'order_id' => $orderId,
            'order_description' => $description,
            'is_fixed_rate' => AdminSetting::getSetting('nowpayments_fixed_rate', '0') === '1',
            'is_fee_paid_by_user' => AdminSetting::getSetting('nowpayments_fee_paid_by_user', '0') === '1',
        ];
        try {
            $response = Http::acceptJson()->asJson()->withHeaders(['x-api-key' => $this->apiKey])
                ->timeout(15)->post($this->baseUrl . '/payment', $payload);
        } catch (ConnectionException $e) {
            throw new NowPaymentsApiException('NOWPayments is temporarily unreachable.', 0, $e);
        }
        if (!$response->successful()) {
            Log::error('NOWPayments payment creation failed', ['environment'=>$this->environment(), 'http_status'=>$response->status(), 'response'=>$response->json()]);
            $this->throwProviderError($response, 'create the payment');
        }
        $data = $response->json();
        if (empty($data['payment_id']) || empty($data['pay_address']) || empty($data['pay_amount'])) {
            Log::error('NOWPayments returned incomplete payment details', ['response'=>$data]);
            throw new \RuntimeException('NOWPayments returned incomplete payment instructions. Please try again or contact support.');
        }
        return $data;
    }

    private function throwProviderError(Response $response, string $action): never
    {
        $code = strtoupper((string)($response->json('code') ?? ''));
        if ($response->status() === 429 || $response->status() >= 500) {
            throw new NowPaymentsApiException('NOWPayments is temporarily unavailable. Please try again shortly.', $response->status());
        }
        if ($code === 'INVALID_API_KEY' || $response->status() === 403) {
            throw new \RuntimeException('NOWPayments rejected the API key for the ' . strtoupper($this->environment()) . ' environment. Check that you entered the API key (not the IPN secret) and that Sandbox Mode matches the key.');
        }

        $message = trim(strip_tags((string)($response->json('message') ?? '')));
        $suffix = $message !== '' ? ' ' . mb_substr($message, 0, 220) : '';
        $errorCode = $code !== '' ? ' (' . $code . ')' : '';
        throw new \RuntimeException('NOWPayments could not ' . $action . $errorCode . '.' . $suffix . ' Check the selected coin/network and the provider minimum.');
    }

    public function paymentStatus(string $paymentId): array
    {
        // Continue polling existing payments if an admin temporarily disables new deposits.
        if (!$this->isConfigured()) throw new \RuntimeException('NOWPayments is not fully configured.');
        $response = Http::acceptJson()->withHeaders(['x-api-key'=>$this->apiKey])
            ->timeout(10)->get($this->baseUrl . '/payment/' . rawurlencode($paymentId));
        if (!$response->successful()) throw new \RuntimeException('Could not refresh NOWPayments status.');
        return $response->json() ?? [];
    }

    public function verifyIpn(array $payload, ?string $signature): bool
    {
        if (!$this->isConfigured() || !$signature) return false;
        $sorted = $this->sortKeys($payload);
        $json = json_encode($sorted, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (!is_string($json)) return false;
        $expected = hash_hmac('sha512', $json, $this->ipnSecret);
        return hash_equals(strtolower($expected), strtolower(trim($signature)));
    }

    private function sortKeys(array $value): array
    {
        ksort($value);
        foreach ($value as $key=>$item) if (is_array($item)) $value[$key] = $this->sortKeys($item);
        return $value;
    }
}
