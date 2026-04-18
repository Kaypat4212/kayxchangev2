<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GiftCardRate extends Model
{
    protected $table = 'gift_card_rates';

    protected $fillable = [
        'name',
        'country',
        'currency',
        'category',
        'buy_rate',
        'sell_rate',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'buy_rate'  => 'float',
        'sell_rate' => 'float',
    ];

    /** Default gift cards seeded on first setup */
    public static function defaultCards(): array
    {
        return [
            // ── Retail ──
            ['name' => 'Amazon',     'country' => 'US', 'currency' => 'USD', 'category' => 'retail'],
            ['name' => 'Amazon',     'country' => 'UK', 'currency' => 'GBP', 'category' => 'retail'],
            ['name' => 'Amazon',     'country' => 'CA', 'currency' => 'CAD', 'category' => 'retail'],
            ['name' => 'Amazon',     'country' => 'AU', 'currency' => 'AUD', 'category' => 'retail'],
            ['name' => 'Amazon',     'country' => 'DE', 'currency' => 'EUR', 'category' => 'retail'],
            ['name' => 'Walmart',    'country' => 'US', 'currency' => 'USD', 'category' => 'retail'],
            ['name' => 'Walmart',    'country' => 'CA', 'currency' => 'CAD', 'category' => 'retail'],
            ['name' => 'Target',     'country' => 'US', 'currency' => 'USD', 'category' => 'retail'],
            ['name' => 'Best Buy',   'country' => 'US', 'currency' => 'USD', 'category' => 'retail'],
            ['name' => 'Best Buy',   'country' => 'CA', 'currency' => 'CAD', 'category' => 'retail'],
            ['name' => 'eBay',       'country' => 'US', 'currency' => 'USD', 'category' => 'retail'],
            ['name' => 'eBay',       'country' => 'UK', 'currency' => 'GBP', 'category' => 'retail'],
            ['name' => 'eBay',       'country' => 'AU', 'currency' => 'AUD', 'category' => 'retail'],
            ['name' => 'Nordstrom',  'country' => 'US', 'currency' => 'USD', 'category' => 'retail'],
            ['name' => 'Nike',       'country' => 'US', 'currency' => 'USD', 'category' => 'retail'],
            ['name' => 'Nike',       'country' => 'UK', 'currency' => 'GBP', 'category' => 'retail'],
            ['name' => 'Sephora',    'country' => 'US', 'currency' => 'USD', 'category' => 'retail'],
            // ── Gaming ──
            ['name' => 'iTunes / Apple', 'country' => 'US', 'currency' => 'USD', 'category' => 'gaming'],
            ['name' => 'iTunes / Apple', 'country' => 'UK', 'currency' => 'GBP', 'category' => 'gaming'],
            ['name' => 'iTunes / Apple', 'country' => 'CA', 'currency' => 'CAD', 'category' => 'gaming'],
            ['name' => 'iTunes / Apple', 'country' => 'AU', 'currency' => 'AUD', 'category' => 'gaming'],
            ['name' => 'iTunes / Apple', 'country' => 'DE', 'currency' => 'EUR', 'category' => 'gaming'],
            ['name' => 'Google Play', 'country' => 'US', 'currency' => 'USD', 'category' => 'gaming'],
            ['name' => 'Google Play', 'country' => 'UK', 'currency' => 'GBP', 'category' => 'gaming'],
            ['name' => 'Google Play', 'country' => 'CA', 'currency' => 'CAD', 'category' => 'gaming'],
            ['name' => 'Google Play', 'country' => 'AU', 'currency' => 'AUD', 'category' => 'gaming'],
            ['name' => 'Google Play', 'country' => 'DE', 'currency' => 'EUR', 'category' => 'gaming'],
            ['name' => 'Steam',      'country' => 'US', 'currency' => 'USD', 'category' => 'gaming'],
            ['name' => 'Steam',      'country' => 'UK', 'currency' => 'GBP', 'category' => 'gaming'],
            ['name' => 'Steam',      'country' => 'EU', 'currency' => 'EUR', 'category' => 'gaming'],
            ['name' => 'PlayStation', 'country' => 'US', 'currency' => 'USD', 'category' => 'gaming'],
            ['name' => 'PlayStation', 'country' => 'UK', 'currency' => 'GBP', 'category' => 'gaming'],
            ['name' => 'PlayStation', 'country' => 'CA', 'currency' => 'CAD', 'category' => 'gaming'],
            ['name' => 'PlayStation', 'country' => 'AU', 'currency' => 'AUD', 'category' => 'gaming'],
            ['name' => 'PlayStation', 'country' => 'DE', 'currency' => 'EUR', 'category' => 'gaming'],
            ['name' => 'Xbox / Microsoft', 'country' => 'US', 'currency' => 'USD', 'category' => 'gaming'],
            ['name' => 'Xbox / Microsoft', 'country' => 'UK', 'currency' => 'GBP', 'category' => 'gaming'],
            ['name' => 'Xbox / Microsoft', 'country' => 'CA', 'currency' => 'CAD', 'category' => 'gaming'],
            ['name' => 'Xbox / Microsoft', 'country' => 'AU', 'currency' => 'AUD', 'category' => 'gaming'],
            ['name' => 'GameStop',   'country' => 'US', 'currency' => 'USD', 'category' => 'gaming'],
            // ── Streaming ──
            ['name' => 'Netflix',    'country' => 'US', 'currency' => 'USD', 'category' => 'streaming'],
            ['name' => 'Netflix',    'country' => 'UK', 'currency' => 'GBP', 'category' => 'streaming'],
            ['name' => 'Netflix',    'country' => 'CA', 'currency' => 'CAD', 'category' => 'streaming'],
            ['name' => 'Netflix',    'country' => 'AU', 'currency' => 'AUD', 'category' => 'streaming'],
            ['name' => 'Spotify',    'country' => 'US', 'currency' => 'USD', 'category' => 'streaming'],
            ['name' => 'Spotify',    'country' => 'UK', 'currency' => 'GBP', 'category' => 'streaming'],
            ['name' => 'Spotify',    'country' => 'CA', 'currency' => 'CAD', 'category' => 'streaming'],
            // ── Prepaid / Financial ──
            ['name' => 'Vanilla Visa',  'country' => 'US', 'currency' => 'USD', 'category' => 'prepaid'],
            ['name' => 'Vanilla Visa',  'country' => 'UK', 'currency' => 'GBP', 'category' => 'prepaid'],
            ['name' => 'Vanilla Visa',  'country' => 'CA', 'currency' => 'CAD', 'category' => 'prepaid'],
            ['name' => 'American Express', 'country' => 'US', 'currency' => 'USD', 'category' => 'prepaid'],
        ];
    }
}
