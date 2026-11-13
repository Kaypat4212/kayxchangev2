<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillProviderSetting extends Model
{
    protected $fillable = [
        'active_providers', 'vtpass_sandbox', 'vtpass_api_key', 'vtpass_public_key', 'vtpass_secret_key',
        'monnify_sandbox', 'monnify_api_key', 'monnify_secret_key', 'monnify_contract_code',
    ];

    protected $casts = [
        'vtpass_sandbox' => 'boolean',
        'monnify_sandbox' => 'boolean',
        'vtpass_api_key' => 'encrypted',
        'vtpass_public_key' => 'encrypted',
        'vtpass_secret_key' => 'encrypted',
        'monnify_api_key' => 'encrypted',
        'monnify_secret_key' => 'encrypted',
    ];

    public function providers(): array
    {
        return array_values(array_intersect(['vtpass', 'monnify'], array_filter(explode(',', (string) $this->active_providers))));
    }

    public static function current(): self
    {
        $settings = static::query()->find(1);
        if ($settings) return $settings;
        $settings = new static();
        $settings->setAttribute('id', 1);
        return $settings;
    }
}
