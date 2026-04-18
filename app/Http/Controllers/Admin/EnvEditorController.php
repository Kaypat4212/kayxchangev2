<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class EnvEditorController extends Controller
{
    /**
     * ONLY these keys may be read or written via the admin UI.
     * APP_KEY, DB_*, SESSION_DRIVER, etc. are intentionally excluded.
     */
    private const ALLOWED = [
        // ── App
        'APP_NAME'                 => ['label' => 'App Name',                'group' => 'app',         'type' => 'text'],
        'APP_URL'                  => ['label' => 'App URL',                 'group' => 'app',         'type' => 'url'],

        // ── Telegram
        'TELEGRAM_BOT_TOKEN'       => ['label' => 'Bot Token',               'group' => 'telegram',    'type' => 'password'],
        'TELEGRAM_CHAT_ID'         => ['label' => 'Chat ID',                 'group' => 'telegram',    'type' => 'text'],

        // ── Wallet Addresses
        'WALLET_BTC_ADDRESS'       => ['label' => 'BTC Wallet Address',      'group' => 'wallets',     'type' => 'text'],
        'WALLET_ETH_ADDRESS'       => ['label' => 'ETH Wallet Address',      'group' => 'wallets',     'type' => 'text'],
        'WALLET_SOL_ADDRESS'       => ['label' => 'SOL Wallet Address',      'group' => 'wallets',     'type' => 'text'],
        'WALLET_USDT_ADDRESS'      => ['label' => 'USDT Default Address',    'group' => 'wallets',     'type' => 'text'],
        'WALLET_USDT_ERC20_ADDRESS'=> ['label' => 'USDT ERC20 Address',      'group' => 'wallets',     'type' => 'text'],
        'WALLET_USDT_TRC20_ADDRESS'=> ['label' => 'USDT TRC20 Address',      'group' => 'wallets',     'type' => 'text'],
        'WALLET_USDT_BEP20_ADDRESS'=> ['label' => 'USDT BEP20 Address',      'group' => 'wallets',     'type' => 'text'],

        // ── Trade Alert Rules
        'TRADE_ALERTS_ENABLED'                 => ['label' => 'Alerts Enabled (true/false)',                  'group' => 'trade_alerts', 'type' => 'text'],
        'TRADE_ALERT_NOTIFY_NEW_BUY'          => ['label' => 'Notify New Buy (true/false)',                  'group' => 'trade_alerts', 'type' => 'text'],
        'TRADE_ALERT_NOTIFY_NEW_SELL'         => ['label' => 'Notify New Sell (true/false)',                 'group' => 'trade_alerts', 'type' => 'text'],
        'TRADE_ALERT_NOTIFY_NEW_WITHDRAWAL'   => ['label' => 'Notify New Withdrawal (true/false)',           'group' => 'trade_alerts', 'type' => 'text'],
        'TRADE_ALERT_NOTIFY_STATUS_CHANGES'   => ['label' => 'Notify Status Changes (true/false)',           'group' => 'trade_alerts', 'type' => 'text'],
        'TRADE_ALERT_HIGH_VALUE_NGN_THRESHOLD'=> ['label' => 'High Value NGN Threshold',                     'group' => 'trade_alerts', 'type' => 'text'],
        'TRADE_ALERT_HIGH_RISK_SCORE_THRESHOLD'=> ['label' => 'High Risk Score Threshold (0-100)',           'group' => 'trade_alerts', 'type' => 'text'],
        'TRADE_ALERT_VIP_CHAT_ID'             => ['label' => 'VIP Telegram Chat ID',                         'group' => 'trade_alerts', 'type' => 'text'],
        'TRADE_ALERT_PENDING_SLA_MINUTES'     => ['label' => 'Pending SLA Minutes',                          'group' => 'trade_alerts', 'type' => 'text'],
        'TRADE_ALERT_ESCALATE_AFTER_MINUTES'  => ['label' => 'Escalate After Minutes',                       'group' => 'trade_alerts', 'type' => 'text'],
        'TRADE_ALERT_ESCALATION_CHAT_ID'      => ['label' => 'Escalation Telegram Chat ID',                  'group' => 'trade_alerts', 'type' => 'text'],

        // ── Paystack
        'PAYSTACK_SECRET_KEY'      => ['label' => 'Secret Key',              'group' => 'paystack',    'type' => 'password'],
        'PAYSTACK_PUBLIC_KEY'      => ['label' => 'Public Key',              'group' => 'paystack',    'type' => 'text'],

        // ── Korapay
        'KORAPAY_SECRET_KEY'       => ['label' => 'Secret Key',              'group' => 'korapay',     'type' => 'password'],
        'KORAPAY_PUBLIC_KEY'       => ['label' => 'Public Key',              'group' => 'korapay',     'type' => 'text'],

        // ── Flutterwave
        'FLUTTERWAVE_SECRET_KEY'   => ['label' => 'Secret Key',              'group' => 'flutterwave', 'type' => 'password'],
        'FLUTTERWAVE_PUBLIC_KEY'   => ['label' => 'Public Key',              'group' => 'flutterwave', 'type' => 'text'],
        'FLUTTERWAVE_WEBHOOK_HASH' => ['label' => 'Webhook Hash / Secret',   'group' => 'flutterwave', 'type' => 'password'],

        // ── Mail
        'MAIL_MAILER'              => ['label' => 'Mailer Driver',           'group' => 'mail',        'type' => 'text'],
        'MAIL_HOST'                => ['label' => 'SMTP Host',               'group' => 'mail',        'type' => 'text'],
        'MAIL_PORT'                => ['label' => 'SMTP Port',               'group' => 'mail',        'type' => 'text'],
        'MAIL_USERNAME'            => ['label' => 'SMTP Username',           'group' => 'mail',        'type' => 'text'],
        'MAIL_PASSWORD'            => ['label' => 'SMTP Password',           'group' => 'mail',        'type' => 'password'],
        'MAIL_FROM_ADDRESS'        => ['label' => 'From Address',            'group' => 'mail',        'type' => 'email'],
        'MAIL_FROM_NAME'           => ['label' => 'From Name',               'group' => 'mail',        'type' => 'text'],
        'MAIL_ENCRYPTION'          => ['label' => 'SMTP Encryption',         'group' => 'mail',        'type' => 'text'],
    ];

    private const GROUP_META = [
        'app'          => ['label' => 'Application',   'icon' => 'bi-gear-fill',          'color' => '#60a5fa'],
        'telegram'     => ['label' => 'Telegram Bot',  'icon' => 'bi-telegram',           'color' => '#38bdf8'],
        'wallets'      => ['label' => 'Wallet Addresses', 'icon' => 'bi-wallet2',         'color' => '#22c55e'],
        'trade_alerts' => ['label' => 'Trade Alert Rules', 'icon' => 'bi-bell-fill',      'color' => '#f59e0b'],
        'paystack'     => ['label' => 'Paystack',      'icon' => 'bi-credit-card-fill',   'color' => '#00cc00'],
        'korapay'      => ['label' => 'Korapay',       'icon' => 'bi-wallet2',            'color' => '#a78bfa'],
        'flutterwave'  => ['label' => 'Flutterwave',   'icon' => 'bi-send-fill',          'color' => '#fbbf24'],
        'mail'         => ['label' => 'Mail / SMTP',   'icon' => 'bi-envelope-fill',      'color' => '#f472b6'],
    ];

    public function index()
    {
        $envPath = base_path('.env');
        $envExists = file_exists($envPath);

        // Read current values from the actual .env file (not cached config())
        $current = [];
        if ($envExists) {
            foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                if (str_starts_with(trim($line), '#')) continue;
                if (!str_contains($line, '=')) continue;
                [$k, $v] = explode('=', $line, 2);
                $current[trim($k)] = trim($v, " \t\n\r\0\x0B\"'");
            }
        }

        // Build grouped display, only for allowed keys
        $groups = [];
        foreach (self::ALLOWED as $key => $meta) {
            $groups[$meta['group']][] = [
                'key'   => $key,
                'label' => $meta['label'],
                'type'  => $meta['type'],
                'value' => $current[$key] ?? '',
            ];
        }

        // Payment method enabled flags from site_contents
        $sc = \App\Models\SiteContent::allKeyed();
        $enabledMethods = [
            'bank_transfer' => (bool) ($sc['pm_enabled_bank_transfer'] ?? true),
            'crypto_transfer' => (bool) ($sc['pm_enabled_crypto_transfer'] ?? true),
            'paystack'      => (bool) ($sc['pm_enabled_paystack']       ?? true),
            'korapay'       => (bool) ($sc['pm_enabled_korapay']        ?? true),
            'flutterwave'   => (bool) ($sc['pm_enabled_flutterwave']    ?? true),
        ];

        // Whether each gateway's secret key is configured in .env
        $keysConfigured = [
            'bank_transfer' => true, // no key needed
            'crypto_transfer' => true, // no API key needed
            'paystack'      => !empty($current['PAYSTACK_SECRET_KEY']),
            'korapay'       => !empty($current['KORAPAY_SECRET_KEY']),
            'flutterwave'   => !empty($current['FLUTTERWAVE_SECRET_KEY']),
        ];

        return view('admin.env-editor', [
            'groups'         => $groups,
            'groupMeta'      => self::GROUP_META,
            'enabledMethods' => $enabledMethods,
            'keysConfigured' => $keysConfigured,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'env'   => 'required|array',
            'env.*' => 'nullable|string|max:500',
        ]);

        $submitted = $request->input('env', []);

        // Only process whitelisted keys
        $toWrite = [];
        foreach ($submitted as $key => $value) {
            $key = strtoupper($key);
            if (!array_key_exists($key, self::ALLOWED)) continue;
            $toWrite[$key] = $value ?? '';
        }

        $this->writeEnvValues($toWrite);

        // Clear config cache so new values take effect immediately
        try { Artisan::call('config:clear'); } catch (\Throwable) {}

        return back()->with('success', 'Environment settings saved. Changes are now live.');
    }

    /** Toggle a payment method on/off — called via AJAX POST */
    public function togglePaymentMethod(Request $request)
    {
        $request->validate([
            'method'  => 'required|in:bank_transfer,crypto_transfer,paystack,korapay,flutterwave',
            'enabled' => 'required|boolean',
        ]);

        $key   = 'pm_enabled_' . $request->method;
        $value = $request->boolean('enabled') ? '1' : '0';

        \App\Models\SiteContent::where('key', $key)->update(['value' => $value]);

        return response()->json(['success' => true, 'method' => $request->method, 'enabled' => (bool) $request->enabled]);
    }

    private function writeEnvValues(array $values): void
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            // Create a minimal .env if it doesn't exist (shouldn't happen in production)
            file_put_contents($envPath, '');
        }

        $content = file_get_contents($envPath);

        foreach ($values as $key => $value) {
            $value = $this->escapeEnvValue($value);

            // Replace existing key (with or without quotes)
            $pattern = '/^' . preg_quote($key, '/') . '\s*=.*/m';

            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, "{$key}={$value}", $content);
            } else {
                // Append new key at end
                $content = rtrim($content) . "\n{$key}={$value}\n";
            }
        }

        file_put_contents($envPath, $content);
    }

    private function escapeEnvValue(string $value): string
    {
        if ($value === '') return '';

        // Wrap in double quotes if value contains spaces, special chars, or #
        if (preg_match('/[\s#"\'\\\\]/', $value)) {
            $value = '"' . addcslashes($value, '"\\') . '"';
        }

        return $value;
    }
}
