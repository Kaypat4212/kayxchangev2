<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillProviderSetting;
use App\Models\AdminSetting;
use App\Services\BillPaymentProviderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class BillProviderSettingsController extends Controller
{
    public function index()
    {
        $settings = BillProviderSetting::current();
        return view('admin.bill-providers.index', [
            'settings' => $settings,
            'billPaymentsEnabled' => filter_var(AdminSetting::getSetting('bill_payments_enabled', '1'), FILTER_VALIDATE_BOOLEAN),
            'utilityPaymentsEnabled' => filter_var(AdminSetting::getSetting('utility_payments_enabled', '0'), FILTER_VALIDATE_BOOLEAN),
            'configured' => [
                'vtpass' => filled($settings->vtpass_api_key) && filled($settings->vtpass_public_key) && filled($settings->vtpass_secret_key),
                'monnify' => filled($settings->monnify_api_key) && filled($settings->monnify_secret_key) && filled($settings->monnify_contract_code),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'providers' => 'nullable|array', 'providers.*' => 'in:vtpass,monnify',
            'vtpass_api_key' => 'nullable|string|max:500', 'vtpass_public_key' => 'nullable|string|max:500',
            'vtpass_secret_key' => 'nullable|string|max:500', 'monnify_api_key' => 'nullable|string|max:500',
            'monnify_secret_key' => 'nullable|string|max:500', 'monnify_contract_code' => 'nullable|string|max:100',
        ]);

        $settings = BillProviderSetting::current();
        $values = [
            'active_providers' => implode(',', array_values(array_unique($data['providers'] ?? []))),
            'vtpass_sandbox' => $request->boolean('vtpass_sandbox'),
            'monnify_sandbox' => $request->boolean('monnify_sandbox'),
        ];
        foreach (['vtpass_api_key','vtpass_public_key','vtpass_secret_key','monnify_api_key','monnify_secret_key','monnify_contract_code'] as $key) {
            if (filled($data[$key] ?? null)) $values[$key] = trim($data[$key]);
        }

        foreach (['vtpass', 'monnify'] as $provider) {
            if (in_array($provider, $values['active_providers'] ? explode(',', $values['active_providers']) : [], true)) {
                $required = $provider === 'vtpass'
                    ? ['vtpass_api_key', 'vtpass_public_key', 'vtpass_secret_key']
                    : ['monnify_api_key', 'monnify_secret_key', 'monnify_contract_code'];
                foreach ($required as $key) {
                    if (!filled($values[$key] ?? $settings->{$key})) {
                        return back()->withErrors([$key => 'Enter all required credentials before enabling ' . ucfirst($provider) . '.']);
                    }
                }
            }
        }

        DB::transaction(function () use ($settings, $values): void {
            $settings->fill($values);
            $settings->save();
        });
        AdminSetting::set('bill_payments_enabled', $request->boolean('bill_payments_enabled') ? '1' : '0');
        AdminSetting::set('utility_payments_enabled', $request->boolean('utility_payments_enabled') ? '1' : '0');

        // Keep cPanel's environment source in sync as requested. Runtime reads
        // encrypted DB values first; the .env entries are private deployment backup.
        $env = [];
        $map = [
            'VTPASS_API_KEY'=>'vtpass_api_key', 'VTPASS_PUBLIC_KEY'=>'vtpass_public_key', 'VTPASS_SECRET_KEY'=>'vtpass_secret_key',
            'MONNIFY_API_KEY'=>'monnify_api_key', 'MONNIFY_SECRET_KEY'=>'monnify_secret_key', 'MONNIFY_CONTRACT_CODE'=>'monnify_contract_code',
        ];
        foreach ($map as $envKey => $dbKey) $env[$envKey] = (string)($settings->{$dbKey} ?? '');
        $env['VTPASS_SANDBOX'] = $settings->vtpass_sandbox ? 'true' : 'false';
        $env['MONNIFY_SANDBOX'] = $settings->monnify_sandbox ? 'true' : 'false';
        $envPath = base_path('.env');
        $contents = is_file($envPath) ? file_get_contents($envPath) : '';
        foreach ($env as $key => $value) {
            $encoded = $value === '' ? '' : '"' . str_replace(["\\", '"', "\r", "\n"], ["\\\\", '\\"', '', ''], $value) . '"';
            $pattern = '/^' . preg_quote($key, '/') . '=.*/m';
            $line = $key . '=' . $encoded;
            $contents = preg_match($pattern, $contents)
                ? preg_replace_callback($pattern, static fn () => $line, $contents, 1)
                : rtrim($contents) . "\n{$line}\n";
        }
        file_put_contents($envPath, $contents, LOCK_EX);
        @chmod($envPath, 0600);
        try { Artisan::call('config:clear'); } catch (\Throwable) {}

        return back()->with('success', 'Bill payment settings saved. Provider secrets are encrypted in the database and synced to the private .env file.');
    }

    public function test(Request $request, BillPaymentProviderService $providers)
    {
        $data = $request->validate(['provider' => 'required|in:vtpass,monnify']);
        try {
            $result = $providers->test($data['provider']);
            $masterEnabled = $providers->billPaymentsEnabled();
            $enabled = $masterEnabled && in_array($data['provider'], $providers->providers(), true);
            $environment = $providers->isSandbox($data['provider']) ? 'sandbox' : 'live';
            $message = ucfirst($data['provider']) . ' ' . $environment . ' catalog connected.';
            if (!$masterEnabled) $message .= ' Customer bill payments are switched off; enable the customer bill-payment switch to show it on the customer page.';
            elseif (!$enabled) $message .= ' Enable "' . ucfirst($data['provider']) . ' for customers" and save to show it on the customer page.';
            elseif ($environment === 'sandbox') $message .= ' Customers can browse test options, but payments stay disabled until live mode is selected.';
            return response()->json(['ok'=>true, 'message'=>$message, 'count'=>count($result['items'] ?? []), 'enabled'=>$enabled, 'environment'=>$environment]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['ok'=>false, 'message'=>$e->getMessage()], 422);
        }
    }
}
