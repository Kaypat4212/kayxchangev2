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
    public static function get(string $key, mixed $default = null): mixed
    {
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

            // ── AI / OpenAI ────────────────────────────────────────────────
            ['key' => 'openai_api_key',  'group' => 'ai', 'is_encrypted' => true,  'label' => 'OpenAI API Key',    'description' => 'From platform.openai.com → API Keys'],
            ['key' => 'openai_model',    'group' => 'ai', 'is_encrypted' => false, 'label' => 'OpenAI Model',      'description' => 'e.g. gpt-4o-mini  (cheapest), gpt-4o, gpt-3.5-turbo'],

            // ── AI / Groq ──────────────────────────────────────────────────
            ['key' => 'groq_api_key',    'group' => 'ai', 'is_encrypted' => true,  'label' => 'Groq API Key',      'description' => 'From console.groq.com → API Keys (free tier available)'],
            ['key' => 'groq_model',      'group' => 'ai', 'is_encrypted' => false, 'label' => 'Groq Model',        'description' => 'e.g. llama-3.3-70b-versatile, llama-3.1-8b-instant, mixtral-8x7b-32768'],

            // ── AI General ────────────────────────────────────────────────
            ['key' => 'ai_provider',     'group' => 'ai', 'is_encrypted' => false, 'label' => 'Active AI Provider','description' => 'openai or groq — which provider the chatbot uses'],
            ['key' => 'ai_chatbot_enabled', 'group' => 'ai', 'is_encrypted' => false, 'label' => 'Enable AI Chatbot', 'description' => '1 = show chatbot widget to logged-in users, 0 = hide'],
            ['key' => 'ai_system_prompt','group' => 'ai', 'is_encrypted' => false, 'label' => 'System Prompt',     'description' => 'Custom instructions for the AI trading assistant'],

            // ── Paystack ───────────────────────────────────────────────────
            ['key' => 'paystack_public_key',  'group' => 'payment', 'is_encrypted' => false, 'label' => 'Paystack Public Key',  'description' => ''],
            ['key' => 'paystack_secret_key',  'group' => 'payment', 'is_encrypted' => true,  'label' => 'Paystack Secret Key',  'description' => ''],

            // ── Telegram ───────────────────────────────────────────────────
            ['key' => 'telegram_token',        'group' => 'telegram', 'is_encrypted' => true,  'label' => 'Bot Token',          'description' => 'From @BotFather'],
            ['key' => 'telegram_owner_chat_id','group' => 'telegram', 'is_encrypted' => false, 'label' => 'Owner Chat ID',      'description' => 'Your personal Telegram chat ID for admin alerts'],

            // ── General ────────────────────────────────────────────────────
            ['key' => 'site_maintenance',  'group' => 'general', 'is_encrypted' => false, 'label' => 'Maintenance Mode', 'description' => '1 = maintenance, 0 = live'],
            ['key' => 'support_whatsapp',  'group' => 'general', 'is_encrypted' => false, 'label' => 'Support WhatsApp', 'description' => 'Phone number for support WhatsApp button'],
        ];

        foreach ($defaults as $def) {
            static::firstOrCreate(['key' => $def['key']], array_merge($def, ['value' => null]));
        }
    }
}
