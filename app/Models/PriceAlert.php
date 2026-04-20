<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceAlert extends Model
{
    protected $fillable = [
        'user_id', 'type', 'coin', 'direction', 'target_price',
        'notify_telegram', 'notify_email', 'notify_app',
        'is_active', 'triggered_at',
    ];

    protected $casts = [
        'target_price'     => 'decimal:2',
        'notify_telegram'  => 'boolean',
        'notify_email'     => 'boolean',
        'notify_app'       => 'boolean',
        'is_active'        => 'boolean',
        'triggered_at'     => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Label for this alert's price unit */
    public function priceUnit(): string
    {
        return $this->type === 'platform' ? 'NGN' : 'USD';
    }
}
