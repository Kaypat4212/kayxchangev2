<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use App\Models\User;
use App\Services\CryptoDepositConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class CryptoDepositSettingsController extends Controller
{
    public function index(CryptoDepositConfiguration $configuration)
    {
        $sellAssets = $configuration->sellAssets();
        return view('admin.crypto-deposit-settings', [
            'assets'=>$configuration->assets(),
            'sellAssets'=>$sellAssets,
            'walletSettings'=>$configuration->walletAddressSettings(),
            'sellWalletSettings'=>$configuration->walletAddressSettings($sellAssets),
            'fallbackEnabled'=>$configuration->fallbackEnabled(),
            'fixedRate'=>AdminSetting::getSetting('nowpayments_fixed_rate', '0') === '1',
            'feePaidByUser'=>AdminSetting::getSetting('nowpayments_fee_paid_by_user', '0') === '1',
            'expiryHours'=>(int)AdminSetting::getSetting('crypto_deposit_expiry_hours', '24'),
            'paymentWindowMinutes'=>(int)AdminSetting::getSetting('nowpayments_payment_window_minutes', '45'),
            'adminReviewEnabled'=>filter_var(AdminSetting::getSetting('crypto_deposit_admin_review_enabled', '1'), FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    public function providerMinimums(CryptoDepositConfiguration $configuration, \App\Services\CryptoWalletService $wallets, \App\Services\NowPaymentsService $provider)
    {
        $minimums = [];
        foreach ($configuration->assets() as $currency => $asset) {
            foreach ($asset['networks'] as $network) {
                $minimum = null;
                try {
                    $code = $wallets->nowPaymentsCurrencyCode($currency, $network);
                    $value = $provider->minimumUsd($code);
                    if ($value > 0) $minimum = round($value, 2);
                } catch (\Throwable $exception) {
                    \Illuminate\Support\Facades\Log::warning('Admin could not fetch a NOWPayments network minimum.', [
                        'currency' => $currency,
                        'network' => $network,
                        'error' => $exception->getMessage(),
                    ]);
                }
                $minimums[$currency][$network] = [
                    'provider_minimum_usd' => $minimum,
                    'admin_floor_usd' => $configuration->minimumUsd($currency, $network),
                    'effective_minimum_usd' => max($configuration->minimumUsd($currency, $network), $minimum ?? 0),
                ];
            }
        }

        return response()->json([
            'ok' => true,
            'environment' => $provider->environment(),
            'minimums' => $minimums,
            'updated_at' => now()->toIso8601String(),
        ])->header('Cache-Control', 'private, no-store, max-age=0');
    }

    public function update(Request $request, CryptoDepositConfiguration $configuration)
    {
        $request->validate([
            'minimums'=>'nullable|array', 'minimums.*'=>'nullable|array', 'minimums.*.*'=>'nullable|numeric|min:0|max:10000000',
            'addresses'=>'nullable|array', 'addresses.*'=>'nullable|array', 'addresses.*.*'=>'nullable|string|max:255',
            'sell_addresses'=>'nullable|array', 'sell_addresses.*'=>'nullable|array', 'sell_addresses.*.*'=>'nullable|string|max:255',
            'wallet_change_pin'=>'nullable|digits:4',
            'fallback_enabled'=>'nullable|boolean', 'nowpayments_fixed_rate'=>'nullable|boolean',
            'nowpayments_fee_paid_by_user'=>'nullable|boolean', 'expiry_hours'=>'required|integer|min:1|max:72',
            'payment_window_minutes'=>'required|integer|min:30|max:60',
            'admin_review_enabled'=>'nullable|boolean',
        ]);

        $minimums = $request->input('minimums', []);
        $addresses = $request->input('addresses', []);
        $changedWallets = [];
        foreach ($configuration->assets() as $currency=>$asset) {
            foreach ($asset['networks'] as $network) {
                $newAddress = trim((string)data_get($addresses, "{$currency}.{$network}", $configuration->fallbackAddress($currency, $network)));
                if ($newAddress !== $configuration->fallbackAddress($currency, $network)) {
                    if ($newAddress !== '' && !$this->validAddress($currency, $network, $newAddress)) {
                        return back()->withInput()->withErrors(["addresses.{$currency}.{$network}"=>'That address format does not match the selected asset network.']);
                    }
                    $changedWallets[$currency . '_' . $network] = [
                        'key'=>$configuration->walletAddressKey($currency, $network),
                        'value'=>$newAddress,
                        'previous'=>$configuration->fallbackAddress($currency, $network),
                    ];
                }
            }
        }
        $providerAssets = $configuration->assets();
        foreach ($configuration->sellAssets() as $currency=>$asset) {
            if (isset($providerAssets[$currency])) continue;
            foreach ($asset['networks'] as $network) {
                $newAddress = trim((string)data_get($request->input('sell_addresses', []), "{$currency}.{$network}", $configuration->fallbackAddress($currency, $network)));
                if ($newAddress !== $configuration->fallbackAddress($currency, $network)) {
                    if ($newAddress !== '' && !$this->validAddress($currency, $network, $newAddress)) {
                        return back()->withInput()->withErrors(["sell_addresses.{$currency}.{$network}"=>'That address format does not match the selected asset network.']);
                    }
                    $changedWallets[$currency . '_' . $network] = [
                        'key'=>$configuration->walletAddressKey($currency, $network),
                        'value'=>$newAddress,
                        'previous'=>$configuration->fallbackAddress($currency, $network),
                    ];
                }
            }
        }

        $error = DB::transaction(function () use ($request, $configuration, $minimums, $changedWallets): ?string {
            $admin = User::query()->lockForUpdate()->findOrFail(auth()->id());
            if ($changedWallets) {
                if (!$admin->transaction_pin) return 'Set up your security PIN before changing deposit wallet addresses.';
                if ($admin->pin_locked_until && now()->lt($admin->pin_locked_until)) return 'Your security PIN is temporarily locked. Try again after the lock expires.';
                if (!Hash::check((string)$request->input('wallet_change_pin'), $admin->transaction_pin)) {
                    $admin->pin_attempts = (int)$admin->pin_attempts + 1;
                    if ($admin->pin_attempts >= 5) $admin->pin_locked_until = now()->addMinutes(15);
                    $admin->save();
                    return 'The security PIN is incorrect. Deposit wallet addresses were not changed.';
                }
                $admin->pin_attempts = 0;
                $admin->pin_locked_until = null;
                $admin->save();
            }

            foreach ($configuration->assets() as $currency=>$asset) {
                foreach ($asset['networks'] as $network) {
                    $minimum = (float)data_get($minimums, "{$currency}.{$network}", $configuration->minimumUsd($currency, $network));
                    AdminSetting::set($configuration->minimumUsdKey($currency, $network), number_format($minimum, 2, '.', ''));
                }
            }
            foreach ($changedWallets as $networkKey=>$entry) {
                AdminSetting::set($entry['key'], $entry['value'], true);
                Log::notice('Crypto deposit wallet address changed', [
                    'admin_id'=>$admin->id,
                    'asset_network'=>$networkKey,
                    'previous_fingerprint'=>hash('sha256', $entry['previous']),
                    'new_fingerprint'=>hash('sha256', $entry['value']),
                ]);
            }
            AdminSetting::set('crypto_deposit_fallback_enabled', $request->boolean('fallback_enabled') ? '1' : '0');
            AdminSetting::set('nowpayments_fixed_rate', $request->boolean('nowpayments_fixed_rate') ? '1' : '0');
            AdminSetting::set('nowpayments_fee_paid_by_user', $request->boolean('nowpayments_fee_paid_by_user') ? '1' : '0');
            AdminSetting::set('crypto_deposit_expiry_hours', (string)$request->integer('expiry_hours'));
            AdminSetting::set('nowpayments_payment_window_minutes', (string)$request->integer('payment_window_minutes'));
            AdminSetting::set('crypto_deposit_admin_review_enabled', $request->boolean('admin_review_enabled') ? '1' : '0');
            return null;
        });

        if ($error) return back()->withInput()->withErrors(['settings'=>$error]);
        return back()->with('success', $changedWallets
            ? 'Deposit settings saved. Wallet address changes were PIN verified and recorded in the audit log.'
            : 'Deposit minimums and provider defaults saved.');
    }

    private function validAddress(string $currency, string $network, string $address): bool
    {
        if ($currency === 'BTC') return (bool)preg_match('/^(bc1|[13])[a-zA-HJ-NP-Z0-9]{25,62}$/', $address);
        if ($network === 'ERC20' || $network === 'BEP20') return (bool)preg_match('/^0x[a-fA-F0-9]{40}$/', $address);
        if ($network === 'TRC20') return (bool)preg_match('/^T[1-9A-HJ-NP-Za-km-z]{33}$/', $address);
        if ($network === 'SOL') return (bool)preg_match('/^[1-9A-HJ-NP-Za-km-z]{32,44}$/', $address);
        if ($currency === 'LTC') return (bool)preg_match('/^(ltc1[ac-hj-np-z02-9]{20,}|[LM3][a-km-zA-HJ-NP-Z1-9]{26,33})$/i', $address);
        // New admin-added assets use their ticker as the network until a
        // network-specific validator is introduced. Accept common address
        // encodings while still rejecting whitespace and malformed inputs.
        return strlen($address) >= 20 && strlen($address) <= 255
            && !preg_match('/\s/', $address)
            && (bool)preg_match('/^(0x[a-fA-F0-9]{40}|[1-9A-HJ-NP-Za-km-z]+)$/', $address);
    }
}
