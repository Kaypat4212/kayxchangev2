<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class AdminSetting extends Model
{
    protected $fillable = ['key', 'value', 'is_encrypted', 'group', 'label', 'description'];

    protected $casts = ['is_encrypted' => 'boolean'];

    // ─── Static helpers ───────────────────────────────────────────────

    /** Get a setting value (auto-decrypts encrypted ones). */
    public static function getSetting(string $key, mixed $default = null): mixed
    {
        try {
            $row = static::where('key', $key)->first();
            if (! $row) return $default;
            if ($row->is_encrypted) {
                try {
                    return Crypt::decryptString($row->value ?? '');
                } catch (\Exception $e) {
                    return $default;
                }
            }
            return $row->value;
        } catch (\Exception $e) {
            // Table might not exist yet during initial setup
            return $default;
        }
    }

    /** Set / upsert a setting value (auto-encrypts if is_encrypted flag already set). */
    public static function set(string $key, mixed $value, bool $encrypt = false): void
    {
        $row = static::firstOrNew(['key' => $key]);
        $shouldEncrypt = $encrypt || $row->is_encrypted;
        $row->value = ($shouldEncrypt && $value !== null && $value !== '') ? Crypt::encryptString((string) $value) : $value;
        $row->is_encrypted = $shouldEncrypt;
        $row->save();
    }

    /** Get all settings for a group, keyed by setting key. Values auto-decrypted. */
    public static function getGroup(string $group): array
    {
        return static::where('group', $group)->get()
            ->mapWithKeys(function ($row) {
                if ($row->is_encrypted) {
                    try {
                        $val = ($row->value && $row->value !== '') ? Crypt::decryptString($row->value) : '';
                    } catch (\Exception $e) {
                        $val = '';
                    }
                } else {
                    $val = $row->value;
                }
                return [$row->key => $val];
            })->all();
    }

    /**
     * Seed the default schema rows (empty values) so the admin form always shows all fields.
     * Call this from a seeder or migration.
     */
    public static function seedDefaults(): void
    {
        $defaults = [
            // ── Cloudflare ─────────────────────────────────────────────────
            ['key' => 'cf_api_token',    'group' => 'cloudflare', 'is_encrypted' => true,  'label' => 'API Token',        'description' => 'Cloudflare API Token (Global or scoped to Zone:Edit, Cache Purge)'],
            ['key' => 'cf_zone_id',      'group' => 'cloudflare', 'is_encrypted' => false, 'label' => 'Zone ID',          'description' => 'Found in your Cloudflare dashboard → Overview → right sidebar'],
            ['key' => 'cf_account_id',   'group' => 'cloudflare', 'is_encrypted' => false, 'label' => 'Account ID',       'description' => 'Cloudflare Account ID (right sidebar on any zone page)'],
            ['key' => 'cf_enabled',      'group' => 'cloudflare', 'is_encrypted' => false, 'label' => 'Enable Cloudflare','description' => 'Toggle Cloudflare features on/off (1 = on, 0 = off)'],
            ['key' => 'homepage_captcha_mode', 'group' => 'cloudflare', 'is_encrypted' => false, 'label' => 'Homepage CAPTCHA mode', 'description' => 'Choose Off, custom arithmetic challenge, or Cloudflare Turnstile for homepage access.'],
            ['key' => 'turnstile_site_key', 'group' => 'cloudflare', 'is_encrypted' => false, 'label' => 'Turnstile Site Key', 'description' => 'Cloudflare Dashboard → Turnstile → widget settings. Safe to show publicly.'],
            ['key' => 'turnstile_secret_key', 'group' => 'cloudflare', 'is_encrypted' => true, 'label' => 'Turnstile Secret Key', 'description' => 'Private verification key. Stored encrypted and only used server-side.'],

            // ── AI / OpenAI ────────────────────────────────────────────────
            ['key' => 'openai_api_key',  'group' => 'ai', 'is_encrypted' => true,  'label' => 'OpenAI API Key',    'description' => 'From platform.openai.com → API Keys'],
            ['key' => 'openai_model',    'group' => 'ai', 'is_encrypted' => false, 'label' => 'OpenAI Model',      'description' => 'e.g. gpt-4o-mini  (cheapest), gpt-4o, gpt-3.5-turbo'],

            // ── AI / Groq ──────────────────────────────────────────────────
            ['key' => 'groq_api_key',    'group' => 'ai', 'is_encrypted' => true,  'label' => 'Groq API Key',      'description' => 'From console.groq.com → API Keys (free tier available)'],
            ['key' => 'groq_model',      'group' => 'ai', 'is_encrypted' => false, 'label' => 'Groq Model',        'description' => 'e.g. openai/gpt-oss-120b, openai/gpt-oss-20b, qwen/qwen3.8-27b'],

            // ── AI General ────────────────────────────────────────────────
            ['key' => 'ai_provider',     'group' => 'ai', 'is_encrypted' => false, 'label' => 'Active AI Provider','description' => 'openai or groq — which provider the chatbot uses'],
            ['key' => 'ai_chatbot_enabled', 'group' => 'ai', 'is_encrypted' => false, 'label' => 'Enable AI Chatbot', 'description' => '1 = show chatbot widget to logged-in users, 0 = hide'],
            ['key' => 'ai_system_prompt','group' => 'ai', 'is_encrypted' => false, 'label' => 'System Prompt',     'description' => 'Custom instructions for the AI trading assistant'],

            // ── Paystack ───────────────────────────────────────────────────
            ['key' => 'paystack_public_key',  'group' => 'payment', 'is_encrypted' => false, 'label' => 'Paystack Public Key',  'description' => ''],
            ['key' => 'paystack_secret_key',  'group' => 'payment', 'is_encrypted' => true,  'label' => 'Paystack Secret Key',  'description' => ''],

            // ── Cryptomus ──────────────────────────────────────────────────
            ['key' => 'cryptomus_api_key',     'group' => 'payment', 'is_encrypted' => true,  'label' => 'Cryptomus API Key',     'description' => 'From cryptomus.com → Settings → API Keys'],
            ['key' => 'cryptomus_merchant_id', 'group' => 'payment', 'is_encrypted' => true,  'label' => 'Cryptomus Merchant ID', 'description' => 'Your Cryptomus Merchant ID'],
            ['key' => 'cryptomus_enabled',     'group' => 'payment', 'is_encrypted' => false, 'label' => 'Enable Cryptomus',      'description' => '1 = enable crypto payments, 0 = disable'],
            ['key' => 'nowpayments_api_key', 'group' => 'payment', 'is_encrypted' => true, 'label' => 'NOWPayments API Key', 'description' => 'From your NOWPayments merchant dashboard → API keys'],
            ['key' => 'nowpayments_ipn_secret', 'group' => 'payment', 'is_encrypted' => true, 'label' => 'NOWPayments IPN Secret', 'description' => 'Webhook signature secret from NOWPayments Payment Settings'],
            ['key' => 'nowpayments_account_email', 'group' => 'payment', 'is_encrypted' => true, 'label' => 'NOWPayments Account Email', 'description' => 'Account email used only to obtain short-lived Custody conversion tokens'],
            ['key' => 'nowpayments_account_password', 'group' => 'payment', 'is_encrypted' => true, 'label' => 'NOWPayments Account Password', 'description' => 'Encrypted server-side credential for Custody conversion authentication'],
            ['key' => 'nowpayments_conversion_enabled', 'group' => 'payment', 'is_encrypted' => false, 'label' => 'Enable NOWPayments Custody Swaps', 'description' => 'Allow wallet-to-wallet swaps backed by merchant Custody funds; requires account credentials and available Custody liquidity'],
            ['key' => 'nowpayments_enabled', 'group' => 'payment', 'is_encrypted' => false, 'label' => 'Enable NOWPayments', 'description' => '1 = allow NOWPayments crypto deposits, 0 = disable'],
            ['key' => 'nowpayments_sandbox', 'group' => 'payment', 'is_encrypted' => false, 'label' => 'NOWPayments Sandbox Mode', 'description' => 'Use test environment credentials and simulated transactions when enabled'],
            ['key' => 'crypto_deposit_provider', 'group' => 'payment', 'is_encrypted' => false, 'label' => 'Crypto Deposit Provider', 'description' => 'Choose NOWPayments, Cryptomus, or automatically use the first configured provider'],
            ['key' => 'cryptomus_convert_enabled', 'group' => 'payment', 'is_encrypted' => false, 'label' => 'Enable Swap Feature', 'description' => '1 = show the crypto swap page to users, 0 = hide it'],
            ['key' => 'convert_mode', 'group' => 'payment', 'is_encrypted' => false, 'label' => 'Swap Mode', 'description' => 'Compare rates, process swaps through an available provider, or use admin-processed manual swaps.'],
            ['key' => 'convert_fee_percent', 'group' => 'payment', 'is_encrypted' => false, 'label' => 'Platform Swap Fee (%)', 'description' => 'Your platform fee, added to the NOWPayments provider swap fee. Users see only the combined fee.'],
            ['key' => 'convert_instructions', 'group' => 'payment', 'is_encrypted' => false, 'label' => 'Manual Swap Instructions', 'description' => 'Shown to users in manual mode — e.g. where/how to send the source crypto'],

            // ── Telegram ───────────────────────────────────────────────────
            ['key' => 'telegram_token',        'group' => 'telegram', 'is_encrypted' => true,  'label' => 'Bot Token',          'description' => 'From @BotFather'],
            ['key' => 'telegram_owner_chat_id','group' => 'telegram', 'is_encrypted' => false, 'label' => 'Owner Chat ID',      'description' => 'Your personal Telegram chat ID for admin alerts'],

            // ── General ────────────────────────────────────────────────────
            ['key' => 'site_maintenance',  'group' => 'general', 'is_encrypted' => false, 'label' => 'Maintenance Mode', 'description' => '1 = maintenance, 0 = live'],
            ['key' => 'runtime_mode', 'group' => 'general', 'is_encrypted' => false, 'label' => 'Platform runtime mode', 'description' => 'Production hides debug details, Local shows diagnostics to admins only, Off displays maintenance to customers while preserving admin access and payment webhooks.'],
            ['key' => 'blocked_countries', 'group' => 'general', 'is_encrypted' => false, 'label' => 'Blocked countries', 'description' => 'Choose countries to restrict, then add any other two-letter ISO country codes if needed. Uses Cloudflare country headers or IP geolocation.'],
            ['key' => 'support_whatsapp',  'group' => 'general', 'is_encrypted' => false, 'label' => 'Support WhatsApp', 'description' => 'Phone number for support WhatsApp button'],
        ];

        foreach ($defaults as $def) {
            static::firstOrCreate(['key' => $def['key']], array_merge($def, ['value' => null]));
        }
    }
}
