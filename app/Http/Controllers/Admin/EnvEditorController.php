<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

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

    /** Show the diagnostics page */
    public function diagnostics()
    {
        return view('admin.diagnostics');
    }

    /** Run all diagnostic checks and return JSON results */
    public function runDiagnostics(\Illuminate\Http\Request $request)
    {
        set_time_limit(30);
        $results = [];

        // ── 1. SMTP / Email connectivity ──────────────────────────────────────
        $results['smtp'] = $this->checkSmtp();

        // ── 2. Telegram Bot Token ─────────────────────────────────────────────
        $results['telegram'] = $this->checkTelegram();

        // ── 3. Paystack ───────────────────────────────────────────────────────
        $results['paystack'] = $this->checkPaystack();

        // ── 4. Groq AI ────────────────────────────────────────────────────────
        $results['groq'] = $this->checkGroq();

        // ── 5. Etherscan ─────────────────────────────────────────────────────
        $results['etherscan'] = $this->checkEtherscan();

        // ── 6. BlockCypher ───────────────────────────────────────────────────
        $results['blockcypher'] = $this->checkBlockCypher();

        // ── 7. TronGrid ──────────────────────────────────────────────────────
        $results['trongrid'] = $this->checkTronGrid();

        // ── 8. PHP mail() function enabled ───────────────────────────────────
        $results['php_mail'] = $this->checkPhpMail();

        // ── 9. cURL / outbound HTTP ──────────────────────────────────────────
        $results['curl'] = $this->checkCurl();

        // ── 10. Database ─────────────────────────────────────────────────────
        $results['database'] = $this->checkDatabase();

        return response()->json($results);
    }

    private function checkSmtp(): array
    {
        $host = env('MAIL_HOST', '');
        $port = (int) env('MAIL_PORT', 465);
        $user = env('MAIL_USERNAME', '');
        $pass = env('MAIL_PASSWORD', '');
        $enc  = env('MAIL_ENCRYPTION', 'ssl');
        $mailer = env('MAIL_MAILER', 'log');

        if ($mailer === 'log') {
            return ['status' => 'warn', 'message' => 'MAIL_MAILER is set to "log" — emails are written to log files, not actually sent. Change to "smtp" in your .env.'];
        }
        if (empty($host)) {
            return ['status' => 'fail', 'message' => 'MAIL_HOST is not set.'];
        }
        if (empty($user)) {
            return ['status' => 'fail', 'message' => 'MAIL_USERNAME is not set.'];
        }
        if (empty($pass)) {
            return ['status' => 'fail', 'message' => 'MAIL_PASSWORD is empty — SMTP authentication will fail.'];
        }

        // Test TCP socket connection to the SMTP host/port
        $errno  = 0; $errstr = '';
        $prefix = ($enc === 'ssl') ? 'ssl://' : '';
        $conn   = @fsockopen($prefix . $host, $port, $errno, $errstr, 8);
        if (!$conn) {
            return [
                'status'  => 'fail',
                'message' => "Cannot connect to {$host}:{$port} — {$errstr} (errno {$errno}). cPanel SMTP may be blocked by firewall or the host/port is wrong.",
                'detail'  => "Tried: {$prefix}{$host}:{$port}",
            ];
        }
        fclose($conn);

        // Check FROM domain matches USERNAME domain (cPanel anti-spoofing)
        $from      = env('MAIL_FROM_ADDRESS', '');
        $fromDomain = strtolower(substr(strrchr($from, '@'), 1));
        $userDomain = strtolower(substr(strrchr($user, '@'), 1));
        if ($fromDomain && $userDomain && $fromDomain !== $userDomain) {
            return [
                'status'  => 'warn',
                'message' => "TCP connection to {$host}:{$port} OK, but FROM domain ({$fromDomain}) ≠ USERNAME domain ({$userDomain}). cPanel will reject with 550 spam error.",
            ];
        }

        return ['status' => 'ok', 'message' => "TCP connection to {$host}:{$port} successful. Credentials are set. FROM domain matches. Ready to send."];
    }

    private function checkTelegram(): array
    {
        $token = env('KAYXCHANGE_TELEGRAM_BOT_TOKEN', env('TELEGRAM_BOT_TOKEN', ''));
        if (empty($token)) {
            return ['status' => 'fail', 'message' => 'TELEGRAM_BOT_TOKEN is not set.'];
        }
        try {
            $res = \Illuminate\Support\Facades\Http::timeout(8)
                ->get("https://api.telegram.org/bot{$token}/getMe");
            if ($res->ok() && ($res->json('ok') === true)) {
                $bot = $res->json('result');
                return ['status' => 'ok', 'message' => "Bot valid — @{$bot['username']} ({$bot['first_name']})"];
            }
            return ['status' => 'fail', 'message' => 'Invalid token: ' . ($res->json('description') ?? 'Unknown error')];
        } catch (\Throwable $e) {
            return ['status' => 'fail', 'message' => 'Request failed: ' . $e->getMessage()];
        }
    }

    private function checkPaystack(): array
    {
        $key = env('PAYSTACK_SECRET_KEY', '');
        if (empty($key)) return ['status' => 'warn', 'message' => 'PAYSTACK_SECRET_KEY not set.'];
        try {
            $res = \Illuminate\Support\Facades\Http::timeout(8)
                ->withToken($key)
                ->get('https://api.paystack.co/bank?perPage=1');
            if ($res->ok() && $res->json('status') === true) {
                return ['status' => 'ok', 'message' => 'Paystack API key is valid.'];
            }
            return ['status' => 'fail', 'message' => 'Invalid key: ' . ($res->json('message') ?? $res->status())];
        } catch (\Throwable $e) {
            return ['status' => 'fail', 'message' => 'Request failed: ' . $e->getMessage()];
        }
    }

    private function checkGroq(): array
    {
        $key   = env('GROQ_API_KEY', '');
        $model = env('GROQ_MODEL', 'llama-3.3-70b-versatile');
        if (empty($key)) return ['status' => 'warn', 'message' => 'GROQ_API_KEY not set — AI features disabled.'];
        try {
            $res = \Illuminate\Support\Facades\Http::timeout(12)
                ->withToken($key)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model'      => $model,
                    'messages'   => [['role' => 'user', 'content' => 'Reply with the single word: OK']],
                    'max_tokens' => 5,
                ]);
            if ($res->ok()) {
                $reply = $res->json('choices.0.message.content') ?? '(no content)';
                return ['status' => 'ok', 'message' => "Groq API valid. Model: {$model}. Reply: " . trim($reply)];
            }
            return ['status' => 'fail', 'message' => 'Groq error: ' . ($res->json('error.message') ?? $res->status())];
        } catch (\Throwable $e) {
            return ['status' => 'fail', 'message' => 'Request failed: ' . $e->getMessage()];
        }
    }

    private function checkEtherscan(): array
    {
        $key = env('ETHERSCAN_API_KEY', '');
        if (empty($key)) return ['status' => 'warn', 'message' => 'ETHERSCAN_API_KEY not set.'];
        try {
            $res = \Illuminate\Support\Facades\Http::timeout(8)
                ->get("https://api.etherscan.io/api", [
                    'module' => 'stats', 'action' => 'ethsupply', 'apikey' => $key
                ]);
            if ($res->ok() && ($res->json('status') === '1' || $res->json('message') === 'OK')) {
                return ['status' => 'ok', 'message' => 'Etherscan API key is valid.'];
            }
            $msg = $res->json('message') ?? $res->json('result') ?? 'Unknown';
            if (str_contains(strtolower($msg), 'invalid') || str_contains(strtolower($msg), 'missing')) {
                return ['status' => 'fail', 'message' => "Invalid API key: {$msg}"];
            }
            return ['status' => 'ok', 'message' => "Etherscan responded: {$msg}"];
        } catch (\Throwable $e) {
            return ['status' => 'fail', 'message' => 'Request failed: ' . $e->getMessage()];
        }
    }

    private function checkBlockCypher(): array
    {
        $token = env('BLOCKCYPHER_TOKEN', '');
        if (empty($token)) return ['status' => 'warn', 'message' => 'BLOCKCYPHER_TOKEN not set.'];
        try {
            $res = \Illuminate\Support\Facades\Http::timeout(8)
                ->get("https://api.blockcypher.com/v1/btc/main?token={$token}");
            if ($res->ok() && isset($res->json()['name'])) {
                return ['status' => 'ok', 'message' => 'BlockCypher token valid. Chain: ' . $res->json('name')];
            }
            return ['status' => 'fail', 'message' => 'Invalid token or request failed: ' . $res->status()];
        } catch (\Throwable $e) {
            return ['status' => 'fail', 'message' => 'Request failed: ' . $e->getMessage()];
        }
    }

    private function checkTronGrid(): array
    {
        $key = trim(env('TRONGRID_API_KEY', ''));
        if (empty($key)) return ['status' => 'warn', 'message' => 'TRONGRID_API_KEY not set.'];
        try {
            $res = \Illuminate\Support\Facades\Http::timeout(8)
                ->withHeaders(['TRON-PRO-API-KEY' => $key])
                ->get('https://api.trongrid.io/v1/blocks/latest');
            if ($res->ok() && isset($res->json()['data'])) {
                return ['status' => 'ok', 'message' => 'TronGrid API key valid.'];
            }
            $err = $res->json('Error') ?? $res->json('message') ?? $res->status();
            return ['status' => 'fail', 'message' => "TronGrid error: {$err}"];
        } catch (\Throwable $e) {
            return ['status' => 'fail', 'message' => 'Request failed: ' . $e->getMessage()];
        }
    }

    private function checkPhpMail(): array
    {
        if (!function_exists('mail')) {
            return ['status' => 'fail', 'message' => 'PHP mail() function is disabled on this server. Use SMTP instead.'];
        }
        // Check if sendmail path is configured
        $sendmailPath = ini_get('sendmail_path');
        if (empty($sendmailPath)) {
            return ['status' => 'warn', 'message' => 'PHP mail() exists but sendmail_path is not configured. Use SMTP — it\'s more reliable on cPanel.'];
        }
        return ['status' => 'ok', 'message' => "PHP mail() enabled. sendmail_path: {$sendmailPath}. Note: SMTP is more reliable for cPanel."];
    }

    private function checkCurl(): array
    {
        if (!function_exists('curl_version')) {
            return ['status' => 'fail', 'message' => 'cURL is not available on this server — all external API calls will fail.'];
        }
        $v = curl_version();
        return ['status' => 'ok', 'message' => "cURL {$v['version']} available. SSL: {$v['ssl_version']}"];
    }

    private function checkDatabase(): array
    {
        try {
            \Illuminate\Support\Facades\DB::connection()->getPdo();
            $db = config('database.connections.' . config('database.default') . '.database');
            return ['status' => 'ok', 'message' => "Connected to database: {$db}"];
        } catch (\Throwable $e) {
            return ['status' => 'fail', 'message' => 'DB connection failed: ' . $e->getMessage()];
        }
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
