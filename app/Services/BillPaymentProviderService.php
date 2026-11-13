<?php

namespace App\Services;

use App\Models\BillProviderSetting;
use App\Models\AdminSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/** Thin server-side adapters for VTpass and Monnify Bills Payment APIs. */
class BillPaymentProviderService
{
    public function providers(): array
    {
        if (!$this->billPaymentsEnabled()) return [];
        return BillProviderSetting::current()->providers();
    }

    public function billPaymentsEnabled(): bool
    {
        return filter_var(AdminSetting::getSetting('bill_payments_enabled', '1'), FILTER_VALIDATE_BOOLEAN);
    }

    public function utilityPaymentsEnabled(): bool
    {
        return $this->billPaymentsEnabled()
            && filter_var(AdminSetting::getSetting('utility_payments_enabled', '0'), FILTER_VALIDATE_BOOLEAN);
    }

    public function assertCategoryEnabled(string $category): void
    {
        if (!$this->billPaymentsEnabled()) {
            throw new RuntimeException('Bill payments are coming soon. Please check back later.');
        }
        if (in_array($category, ['electricity', 'cable_tv'], true) && !$this->utilityPaymentsEnabled()) {
            throw new RuntimeException('Utility payments are coming soon. Please check back later.');
        }
    }

    public function isSandbox(string $provider): bool
    {
        if (!in_array($provider, ['vtpass', 'monnify'], true)) {
            throw new RuntimeException('Unknown bill payment provider.');
        }
        return $provider === 'vtpass' ? $this->vtpassSandbox() : $this->monnifySandbox();
    }

    public function assertLiveVending(string $provider): void
    {
        $this->assertEnabled($provider);
        if ($this->isSandbox($provider)) {
            throw new RuntimeException(ucfirst($provider) . ' is in sandbox mode. Bill purchases are disabled until the admin switches it to live mode.');
        }
    }

    public function catalog(string $provider, string $category = '', string $biller = ''): array
    {
        if ($category !== '') $this->assertCategoryEnabled($category);
        $this->assertEnabled($provider);
        if ($provider === 'vtpass') {
            if ($biller !== '') return $this->vtpassGet('/api/service-variations', ['serviceID' => $biller]);
            $identifier = ['airtime'=>'airtime', 'data'=>'data', 'electricity'=>'electricity-bill', 'cable_tv'=>'tv-subscription', 'education'=>'education'][$category] ?? null;
            if (!$identifier) return ['items' => []];
            return $this->vtpassGet('/api/services', ['identifier' => $identifier]);
        }

        if ($category === '' && $biller === '') $body = $this->monnify('GET', '/api/v1/bills/categories');
        elseif ($biller === '') $body = $this->monnify('GET', '/api/v1/bills/billers', ['categoryCode' => strtoupper($category)]);
        else $body = $this->monnify('GET', '/api/v1/bills/products', ['billerCode' => $biller]);
        return ['items'=>data_get($body, 'content', $body), 'raw'=>$body];
    }

    public function validateCustomer(string $provider, array $payload): array
    {
        $this->assertEnabled($provider);
        if ($provider === 'monnify') return $this->monnify('POST', '/api/v1/bills/validate-customer', [], [
            'productCode'=>$payload['product_code'] ?? '', 'customerId'=>$payload['customer_identifier'],
        ]);
        $data = ['serviceID' => $payload['service_code'], 'billersCode' => $payload['customer_identifier']];
        if (!empty($payload['type'])) $data['type'] = $payload['type'];
        return $this->vtpassPost('/api/merchant-verify', $data);
    }

    public function vend(string $provider, array $payload): array
    {
        $this->assertEnabled($provider);
        if ($provider === 'monnify') {
            $data = [
                'productCode' => $payload['product_code'], 'customerId' => $payload['customer_identifier'],
                'vendAmount' => (float) $payload['amount'], 'vendReference' => $payload['reference'],
            ];
            $validationReference = data_get($payload, 'validation_data.vendInstruction.validationReference')
                ?? data_get($payload, 'validation_data.validationReference');
            if ($validationReference) $data['validationReference'] = $validationReference;
            return $this->monnify('POST', '/api/v1/bills/vend', [], $data);
        }

        $data = [
            'request_id' => $payload['reference'], 'serviceID' => $payload['service_code'],
            'billersCode' => $payload['customer_identifier'], 'amount' => (float) $payload['amount'],
            'phone' => $payload['phone'],
        ];
        if (!empty($payload['product_code'])) $data['variation_code'] = $payload['product_code'];
        if (!empty($payload['type'])) $data['type'] = $payload['type'];
        return $this->vtpassPost('/api/pay', $data);
    }

    public function requery(string $provider, string $reference): array
    {
        $this->assertEnabled($provider);
        if ($provider === 'monnify') return $this->monnify('GET', '/api/v1/bills/requery/' . rawurlencode($reference));
        return $this->vtpassPost('/api/requery', ['request_id' => $reference]);
    }

    public function productPrice(string $provider, string $category, string $biller, string $productCode): ?float
    {
        $catalog = $this->catalog($provider, $category, $biller);
        $items = $catalog['items'] ?? [];
        if (isset($items['variations'])) $items = $items['variations'];
        elseif (isset($items['varations'])) $items = $items['varations'];
        foreach (is_array($items) ? $items : [] as $item) {
            $code = (string)($item['productCode'] ?? $item['variation_code'] ?? $item['code'] ?? '');
            if ($code !== $productCode) continue;
            $amount = $item['amount'] ?? $item['variation_amount'] ?? null;
            return is_numeric($amount) ? (float)$amount : null;
        }
        throw new RuntimeException('That bill plan is no longer available. Refresh the options and try again.');
    }

    public function test(string $provider): array
    {
        $this->assertConfigured($provider);
        if ($provider === 'vtpass') return $this->vtpassGet('/api/services', ['identifier'=>'airtime']);
        $body = $this->monnify('GET', '/api/v1/bills/categories');
        return ['items'=>data_get($body, 'content', $body)];
    }

    public function normalizeStatus(string $provider, array $response): string
    {
        if ($provider === 'monnify') {
            $status = strtoupper((string) (data_get($response, 'responseBody.vendStatus') ?? data_get($response, 'data.vendStatus') ?? data_get($response, 'vendStatus')));
            return match ($status) { 'SUCCESS', 'SUCCESSFUL' => 'successful', 'FAILED', 'FAILURE' => 'failed', default => 'pending' };
        }
        $code = (string) ($response['code'] ?? '');
        $status = strtolower((string) (data_get($response, 'content.transactions.status') ?? ''));
        if ($code === '000' || in_array($status, ['delivered', 'successful', 'success'], true)) return 'successful';
        if (in_array($code, ['099', '001'], true) || in_array($status, ['pending', 'processing'], true)) return 'pending';
        return 'failed';
    }

    private function assertEnabled(string $provider): void
    {
        if (!in_array($provider, $this->providers(), true)) throw new RuntimeException('This bill payment provider is currently unavailable.');
        $this->assertConfigured($provider);
    }

    private function assertConfigured(string $provider): void
    {
        $settings = BillProviderSetting::current();
        if ($provider === 'vtpass' && (!$this->vtpassValue('api_key') || !$this->vtpassValue('public_key') || !$this->vtpassValue('secret_key'))) {
            throw new RuntimeException('VTpass is not fully configured.');
        }
        if ($provider === 'monnify' && (!$this->monnifyValue('api_key') || !$this->monnifyValue('secret_key') || !$this->monnifyValue('contract_code'))) {
            throw new RuntimeException('Monnify Bills Payment is not fully configured.');
        }
    }

    private function vtpassGet(string $path, array $query = []): array
    {
        $s = BillProviderSetting::current();
        $base = $this->vtpassSandbox() ? 'https://sandbox.vtpass.com' : 'https://vtpass.com';
        $response = Http::acceptJson()->withHeaders(['api-key'=>$this->vtpassValue('api_key'), 'public-key'=>$this->vtpassValue('public_key')])
            ->timeout(20)->get($base . $path, $query);
        if (!$response->successful()) throw new RuntimeException('VTpass could not load the bill catalogue.');
        $body = $response->json() ?? [];
        return ['items' => data_get($body, 'content', []), 'raw' => $body];
    }

    private function vtpassPost(string $path, array $payload): array
    {
        $s = BillProviderSetting::current();
        $base = $this->vtpassSandbox() ? 'https://sandbox.vtpass.com' : 'https://vtpass.com';
        $response = Http::acceptJson()->asJson()->withHeaders(['api-key'=>$this->vtpassValue('api_key'), 'secret-key'=>$this->vtpassValue('secret_key')])
            ->timeout(30)->post($base . $path, $payload);
        if (!$response->successful()) throw new RuntimeException('VTpass request failed.');
        return $response->json() ?? [];
    }

    private function monnify(string $method, string $path, array $query = [], array $payload = []): array
    {
        $s = BillProviderSetting::current();
        $base = $this->monnifySandbox() ? 'https://sandbox.monnify.com' : 'https://api.monnify.com';
        $apiKey = $this->monnifyValue('api_key');
        $secret = $this->monnifyValue('secret_key');
        $token = Cache::remember('bill_monnify_access_token_' . sha1((string)$apiKey . '|' . (string)$secret), now()->addMinutes(50), function () use ($apiKey, $secret, $base) {
            $response = Http::withBasicAuth((string)$apiKey, (string)$secret)
                ->acceptJson()->timeout(20)->post($base . '/api/v1/auth/login');
            $token = $response->json('responseBody.accessToken');
            if (!$response->successful() || !$token) throw new RuntimeException('Monnify authentication failed. Check the API keys and environment.');
            return $token;
        });
        $request = Http::withToken($token)->acceptJson()->timeout(30);
        $response = $method === 'GET'
            ? $request->get($base . $path, $query)
            : $request->asJson()->post($base . $path, $payload);
        $body = $response->json() ?? [];
        if (!$response->successful() || (isset($body['requestSuccessful']) && !$body['requestSuccessful'])) {
            if (data_get($body, 'responseBody.vendStatus')) return $body['responseBody'];
            throw new RuntimeException((string)($body['responseMessage'] ?? 'Monnify Bills Payment request failed.'));
        }
        return $body['responseBody'] ?? $body;
    }

    private function vtpassValue(string $key): ?string
    {
        $settings = BillProviderSetting::current();
        return $settings->{'vtpass_' . $key} ?: config('services.vtpass.' . $key);
    }

    private function monnifyValue(string $key): ?string
    {
        $settings = BillProviderSetting::current();
        return $settings->{'monnify_' . $key} ?: config('services.monnify.' . $key);
    }

    private function vtpassSandbox(): bool
    {
        $settings = BillProviderSetting::current();
        return $settings->exists ? (bool)$settings->vtpass_sandbox : (bool)config('services.vtpass.sandbox', true);
    }

    private function monnifySandbox(): bool
    {
        $settings = BillProviderSetting::current();
        return $settings->exists ? (bool)$settings->monnify_sandbox : (bool)config('services.monnify.sandbox', true);
    }
}
