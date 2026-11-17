<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BuyCryptoRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        $coin = strtoupper(trim((string) $this->input('coin')));
        $network = $this->input('network');
        $configuredCoins = app(\App\Services\CryptoTradeRateService::class)->configuredCoins();
        $networks = app(\App\Services\CryptoDepositConfiguration::class)->assets()[$coin]['networks'] ?? [];

        return [
            'coin' => ['required', 'string', Rule::in($configuredCoins), function ($attribute, $value, $fail) {
                if (!\App\Models\CryptoRate::query()->whereRaw('UPPER(coin) = ?', [strtoupper((string)$value)])->exists()) $fail('This cryptocurrency is not currently enabled for trading.');
            }],
            'amount' => ['required', 'numeric', 'min:'.($this->input('input_type') === 'usd' ? 10 : 14000)],
            'input_type' => ['required', Rule::in(['usd', 'naira'])],
            'wallet_address' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($coin, $network) {
                    if (!$this->isValidWalletAddress($value, $coin, $network)) {
                        $fail("The wallet address is invalid for $coin on $network network.");
                    }
                },
            ],
            'network' => ['required', 'string', Rule::in($networks)],
        ];
    }

    protected function isValidWalletAddress($address, $coin, $network)
    {
        $address = trim($address);
        $patterns = [
            'BTC' => ['Bitcoin' => '/^(1|3|bc1)[A-Za-z0-9]{25,74}$/'],
            'ETH' => ['Ethereum' => '/^0x[a-fA-F0-9]{40}$/', 'ERC20' => '/^0x[a-fA-F0-9]{40}$/'],
            'USDT' => ['Tron' => '/^T[A-Za-z0-9]{33}$/', 'TRC20' => '/^T[A-Za-z0-9]{33}$/', 'ERC20' => '/^0x[a-fA-F0-9]{40}$/', 'BEP20' => '/^0x[a-fA-F0-9]{40}$/'],
            'USDC' => ['ERC20' => '/^0x[a-fA-F0-9]{40}$/', 'BEP20' => '/^0x[a-fA-F0-9]{40}$/'],
            'LTC' => ['LTC' => '/^(L|M|3)[1-9A-HJ-NP-Za-km-z]{25,34}$|^ltc1[0-9ac-hj-np-z]{20,60}$/i'],
        ];

        if (isset($patterns[$coin][$network])) return (bool) preg_match($patterns[$coin][$network], $address);
        // For admin-added assets without a chain-specific validator, perform
        // conservative syntax checks and require manual staff review as usual.
        return strlen($address) >= 20 && strlen($address) <= 128 && (bool) preg_match('/^[A-Za-z0-9:_-]+$/', $address);
    }
}
