<?php

namespace App\Services;

use App\Exceptions\NowPaymentsApiException;
use App\Models\AdminSetting;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\Response;

class NowPaymentsService
{
    private string $apiKey;
    private string $ipnSecret;
    private string $accountEmail;
    private string $accountPassword;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = trim((string) AdminSetting::getSetting('nowpayments_api_key', ''));
        $this->ipnSecret = trim((string) AdminSetting::getSetting('nowpayments_ipn_secret', ''));
        $this->accountEmail = trim((string) AdminSetting::getSetting('nowpayments_account_email', ''));
        $this->accountPassword = (string) AdminSetting::getSetting('nowpayments_account_password', '');
        $sandbox = AdminSetting::getSetting('nowpayments_sandbox', '1') !== '0';
        $this->baseUrl = $sandbox ? 'https://api-sandbox.nowpayments.io/v1' : 'https://api.nowpayments.io/v1';
    }

    public function isEnabled(): bool
    {
        return AdminSetting::getSetting('nowpayments_enabled', '0') === '1' && $this->isConfigured();
    }

    public function isConfigured(): bool { return $this->apiKey !== '' && $this->ipnSecret !== ''; }

    public function isCustodyConversionReady(): bool
    {
        return AdminSetting::getSetting('nowpayments_conversion_enabled', '0') === '1'
            && $this->hasCustodyCredentials();
    }

    private function hasCustodyCredentials(): bool
    {
        return $this->apiKey !== '' && $this->accountEmail !== '' && $this->accountPassword !== '';
    }

    /** Obtain a short-lived JWT for authenticated Custody endpoints. */
    private function custodyToken(): string
    {
        if (!$this->hasCustodyCredentials()) {
            throw new \RuntimeException('NOWPayments Custody API credentials are not fully configured.');
        }

        $cacheKey = 'nowpayments_custody_jwt_' . hash('sha256', $this->environment() . '|' . strtolower($this->accountEmail));
        return Cache::remember($cacheKey, now()->addMinutes(4), function (): string {
            try {
                $response = Http::acceptJson()->asJson()->timeout(12)
                    ->post($this->baseUrl . '/auth', [
                        'email' => $this->accountEmail,
                        'password' => $this->accountPassword,
                    ]);
            } catch (ConnectionException $e) {
                throw new NowPaymentsApiException('NOWPayments Custody is temporarily unreachable.', 0, $e);
            }

            if (!$response->successful() || !is_string($response->json('token'))) {
                if (in_array($response->status(), [401, 403], true)) {
                    throw new \RuntimeException('NOWPayments rejected the Custody account login. Verify the account email and password exactly (they are case-sensitive) and that they belong to the selected LIVE or Sandbox account. This login is separate from the API key check.');
                }
                $this->throwProviderError($response, 'authenticate for Custody conversions');
            }

            return $response->json('token');
        });
    }

    public function prepareCustodyAccess(): void
    {
        $this->custodyToken();
    }

    /** Create a merchant-Custody conversion; this does not send funds to an external address. */
    public function createCustodyConversion(string $fromCurrency, string $toCurrency, string $amount): array
    {
        if (!$this->isCustodyConversionReady()) throw new \RuntimeException('NOWPayments Custody swaps are currently disabled or not fully configured.');
        $token = $this->custodyToken();
        try {
            $response = Http::acceptJson()->asJson()->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'x-api-key' => $this->apiKey,
            ])->timeout(20)->post($this->baseUrl . '/conversion', [
                'from_currency' => strtolower($fromCurrency),
                'to_currency' => strtolower($toCurrency),
                'amount' => (float) $amount,
            ]);
        } catch (ConnectionException $e) {
            // A timeout is ambiguous: the provider may have accepted the swap.
            throw new NowPaymentsApiException('NOWPayments did not confirm whether the conversion was created. Reconcile the request before retrying.', 0, $e);
        }

        if (!$response->successful()) {
            if (in_array($response->status(), [401, 403], true)) {
                throw new \RuntimeException('NOWPayments denied access to the Custody conversion endpoint. The regular payment API key can still be valid; ask NOWPayments to enable Custody conversions for this account and confirm the login belongs to the same environment.');
            }
            $this->throwProviderError($response, 'create the Custody conversion');
        }
        return $response->json() ?? [];
    }

    public function custodyConversionStatus(string $conversionId): array
    {
        $token = $this->custodyToken();
        try {
            $response = Http::acceptJson()->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'x-api-key' => $this->apiKey,
            ])->timeout(12)->get($this->baseUrl . '/conversion/' . rawurlencode($conversionId));
        } catch (ConnectionException $e) {
            throw new NowPaymentsApiException('NOWPayments Custody is temporarily unreachable.', 0, $e);
        }

        if (!$response->successful()) {
            if (in_array($response->status(), [401, 403], true)) {
                throw new \RuntimeException('NOWPayments denied access to the Custody conversion status. Confirm Custody access is enabled for this account and the saved LIVE or Sandbox credentials still match.');
            }
            $this->throwProviderError($response, 'refresh the Custody conversion');
        }
        return $response->json() ?? [];
    }

    /** Live provider-side reserve for a network-specific asset code. */
    public function custodyAvailableBalance(string $currencyCode): float
    {
        if ($this->apiKey === '') throw new \RuntimeException('NOWPayments API key is not configured.');
        try {
            $response = Http::acceptJson()->withHeaders(['x-api-key' => $this->apiKey])
                ->timeout(12)->get($this->baseUrl . '/balance');
        } catch (ConnectionException $e) {
            throw new NowPaymentsApiException('NOWPayments balance service is temporarily unreachable.', 0, $e);
        }
        if (!$response->successful()) {
            if (in_array($response->status(), [401, 403], true)) {
                throw new \RuntimeException('NOWPayments denied access to the account balance endpoint. Standard payments may work, but swaps require Custody balance access; ask NOWPayments to enable the required balance/Custody permissions for this account.');
            }
            $this->throwProviderError($response, 'check Custody liquidity');
        }

        $body = $response->json() ?? [];
        $balances = $body['balances'] ?? $body['result'] ?? $body;
        $requestedCode = strtolower($currencyCode);
        $requestedAsset = preg_replace('/(trc20|erc20|bep20|bsc|sol)$/', '', $requestedCode);
        foreach ($balances as $code => $balance) {
            if (!is_array($balance)) continue;
            // /balance reports canonical asset tickers (for example `usdt`),
            // while conversion requests can use a network-qualified currency.
            $balanceCode = strtolower((string) ($balance['currency'] ?? $balance['code'] ?? $balance['ticker'] ?? $code));
            $balanceAsset = preg_replace('/(trc20|erc20|bep20|bsc|sol)$/', '', $balanceCode);
            if ($balanceCode !== $requestedCode && $balanceAsset !== $requestedAsset) continue;
            $amount = (float) ($balance['amount'] ?? $balance['available'] ?? 0);
            $pending = (float) ($balance['pendingAmount'] ?? $balance['pending_amount'] ?? 0);
            return max(0, $amount - max(0, $pending));
        }
        return 0.0;
    }

    public function testCustodyConnection(): array
    {
        $token = $this->custodyToken();
        try {
            $response = Http::acceptJson()->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'x-api-key' => $this->apiKey,
            ])->timeout(12)->get($this->baseUrl . '/conversion', ['limit' => 1]);
        } catch (ConnectionException $e) {
            throw new NowPaymentsApiException('NOWPayments Custody is temporarily unreachable.', 0, $e);
        }
        if (!$response->successful()) {
            if (in_array($response->status(), [401, 403], true)) {
                throw new \RuntimeException('Custody account login succeeded, but NOWPayments denied access to its conversion endpoint. This is separate from regular payment API access; ask NOWPayments to enable Custody conversions for this account.');
            }
            $this->throwProviderError($response, 'verify Custody conversion access');
        }
        // Swaps must not be enabled without the provider liquidity endpoint used
        // to guard internal user liabilities before reserving their balances.
        $balanceResponse = Http::acceptJson()->withHeaders(['x-api-key' => $this->apiKey])->timeout(12)->get($this->baseUrl . '/balance');
        if (!$balanceResponse->successful()) {
            if (in_array($balanceResponse->status(), [401, 403], true)) {
                throw new \RuntimeException('Custody conversion access passed, but NOWPayments denied access to the account balance endpoint. Swaps require this reserve check; ask NOWPayments to enable balance/Custody permissions for this account.');
            }
            $this->throwProviderError($balanceResponse, 'verify Custody balance access');
        }
        return ['environment' => $this->environment()];
    }

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
        $cacheKey = 'nowpayments_minimum_usd_' . $this->environment() . '_' . strtolower($payCurrency) . '_' . hash('sha256', implode('|', [
            AdminSetting::getSetting('nowpayments_fixed_rate', '0'),
            AdminSetting::getSetting('nowpayments_fee_paid_by_user', '0'),
        ]));
        $cachedMinimum = Cache::get($cacheKey);
        try {
            $response = Http::acceptJson()->withHeaders(['x-api-key'=>$this->apiKey])->timeout(10)
                ->get($this->baseUrl . '/min-amount', [
                    'currency_from'=>'usd', 'currency_to'=>strtolower($payCurrency), 'fiat_equivalent'=>'usd',
                    'is_fixed_rate'=>AdminSetting::getSetting('nowpayments_fixed_rate', '0') === '1' ? 'true' : 'false',
                    'is_fee_paid_by_user'=>AdminSetting::getSetting('nowpayments_fee_paid_by_user', '0') === '1' ? 'true' : 'false',
                ]);
        } catch (ConnectionException $e) {
            if (is_numeric($cachedMinimum) && (float) $cachedMinimum > 0) {
                Log::warning('NOWPayments minimum endpoint timed out; using short-lived cached minimum.', ['currency' => strtolower($payCurrency), 'environment' => $this->environment()]);
                return (float) $cachedMinimum;
            }
            throw new NowPaymentsApiException('NOWPayments is temporarily unreachable.', 0, $e);
        }
        if (!$response->successful()) {
            if (($response->status() === 429 || $response->status() >= 500) && is_numeric($cachedMinimum) && (float) $cachedMinimum > 0) {
                Log::warning('NOWPayments minimum endpoint returned a transient error; using short-lived cached minimum.', ['currency' => strtolower($payCurrency), 'environment' => $this->environment(), 'http_status' => $response->status()]);
                return (float) $cachedMinimum;
            }
            $this->throwProviderError($response, 'check the minimum payment amount');
        }
        $fiatMinimum = $response->json('fiat_equivalent');
        if (is_numeric($fiatMinimum)) {
            $minimum = max(0, (float) $fiatMinimum);
            if ($minimum > 0) Cache::put($cacheKey, $minimum, now()->addMinutes(10));
            return $minimum;
        }
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
        if ($code === 'INVALID_API_KEY' || in_array($response->status(), [401, 403], true)) {
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
