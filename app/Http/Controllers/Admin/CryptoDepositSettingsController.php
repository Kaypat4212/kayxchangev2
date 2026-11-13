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
        return view('admin.crypto-deposit-settings', [
            'assets'=>$configuration->assets(),
            'walletSettings'=>$configuration->walletAddressSettings(),
            'fallbackEnabled'=>$configuration->fallbackEnabled(),
            'fixedRate'=>AdminSetting::getSetting('nowpayments_fixed_rate', '0') === '1',
            'feePaidByUser'=>AdminSetting::getSetting('nowpayments_fee_paid_by_user', '0') === '1',
            'expiryHours'=>(int)AdminSetting::getSetting('crypto_deposit_expiry_hours', '24'),
            'adminReviewEnabled'=>filter_var(AdminSetting::getSetting('crypto_deposit_admin_review_enabled', '1'), FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    public function update(Request $request, CryptoDepositConfiguration $configuration)
    {
        $request->validate([
            'minimums'=>'nullable|array', 'minimums.*'=>'nullable|array', 'minimums.*.*'=>'nullable|numeric|min:0|max:10000000',
            'addresses'=>'nullable|array', 'addresses.*'=>'nullable|array', 'addresses.*.*'=>'nullable|string|max:255',
            'wallet_change_pin'=>'nullable|digits:4',
            'fallback_enabled'=>'nullable|boolean', 'nowpayments_fixed_rate'=>'nullable|boolean',
            'nowpayments_fee_paid_by_user'=>'nullable|boolean', 'expiry_hours'=>'required|integer|min:1|max:72',
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
        return false;
    }
}
