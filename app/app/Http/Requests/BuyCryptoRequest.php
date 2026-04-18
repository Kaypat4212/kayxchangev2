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
        $coin = $this->input('coin');
        $network = $this->input('network');

        return [
            'coin' => ['required', Rule::in(['BTC', 'ETH', 'USDT'])],
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
            'network' => ['required', function ($attribute, $value, $fail) use ($coin) {
                $validNetworks = $this->getValidNetworks($coin);
                if (!in_array($value, $validNetworks)) {
                    $fail("The selected network is not compatible with $coin.");
                }
            }],
        ];
    }

    protected function isValidWalletAddress($address, $coin, $network)
    {
        $address = trim($address);

        $patterns = [
            'BTC' => [
                'Bitcoin' => '/^(1|3|bc1)[A-Za-z0-9]{25,74}$/'
            ],
            'ETH' => [
                'Ethereum' => '/^0x[a-fA-F0-9]{40}$/'
            ],
            'USDT' => [
                'Tron' => '/^T[A-Za-z0-9]{33}$/'
            ]
        ];

        if (!isset($patterns[$coin][$network])) {
            return false;
        }

        if (!preg_match($patterns[$coin][$network], $address)) {
            return false;
        }

        return true;
    }

    protected function getValidNetworks($coin)
    {
        return [
            'BTC' => ['Bitcoin'],
            'ETH' => ['Ethereum'],
            'USDT' => ['Tron']
        ][$coin] ?? [];
    }
}