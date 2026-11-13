<?php

namespace App\Services;

use App\Models\AdminSetting;

class CryptoDepositConfiguration
{
    public function assets(): array
    {
        return [
            'BTC'=>['name'=>'Bitcoin','networks'=>['BTC']],
            'ETH'=>['name'=>'Ethereum','networks'=>['ERC20']],
            'USDT'=>['name'=>'Tether','networks'=>['TRC20','ERC20','BEP20']],
            'USDC'=>['name'=>'USD Coin','networks'=>['ERC20','BEP20']],
            'BNB'=>['name'=>'BNB','networks'=>['BEP20']],
            'SOL'=>['name'=>'Solana','networks'=>['SOL']],
        ];
    }

    public function minimumUsdKey(string $currency, string $network): string
    {
        return 'crypto_deposit_min_usd_' . strtolower($currency . '_' . $network);
    }

    public function walletAddressKey(string $currency, string $network): string
    {
        return 'crypto_deposit_wallet_' . strtolower($currency . '_' . $network);
    }

    public function minimumUsd(string $currency, string $network): float
    {
        return max(0, (float)AdminSetting::getSetting($this->minimumUsdKey($currency, $network), '0'));
    }

    public function fallbackEnabled(): bool
    {
        return filter_var(AdminSetting::getSetting('crypto_deposit_fallback_enabled', '0'), FILTER_VALIDATE_BOOLEAN);
    }

    public function fallbackAddress(string $currency, string $network): string
    {
        $key = $this->walletAddressKey($currency, $network);
        $row = AdminSetting::where('key', $key)->first();
        if ($row) return trim((string)AdminSetting::getSetting($key, ''));
        $legacyConfigKey = $this->legacyWalletConfigKey($currency, $network);
        $fallback = (string)config('wallets.' . $legacyConfigKey, '');
        return trim($fallback);
    }

    public function walletAddressSettings(): array
    {
        $settings = [];
        foreach ($this->assets() as $currency=>$asset) {
            foreach ($asset['networks'] as $network) {
                $settings[$currency][$network] = [
                    'minimum_usd' => number_format($this->minimumUsd($currency, $network), 2, '.', ''),
                    'address' => $this->fallbackAddress($currency, $network),
                ];
            }
        }
        return $settings;
    }

    private function legacyWalletConfigKey(string $currency, string $network): string
    {
        if (in_array($currency, ['USDT','USDC'], true)) return $currency . '_' . $network;
        if ($currency === 'BNB') return 'BNB';
        return $currency;
    }
}
