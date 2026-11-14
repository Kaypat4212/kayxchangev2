<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CryptoDeposit extends Model
{
    protected $fillable = [
        'user_id',
        'wallet_id',
        'currency',
        'expected_amount',
        'received_amount',
        'ngn_credited_amount',
        'ngn_rate_used',
        'btc_usd_price',
        'status',
        'cryptomus_payment_id',
        'payment_url',
        'payment_address',
        'payment_data',
        'transaction_hash',
        'expires_at',
        'completed_at',
        'last_provider_check_at',
    ];

    protected $casts = [
        'expected_amount' => 'decimal:8',
        'received_amount' => 'decimal:8',
        'ngn_credited_amount' => 'decimal:2',
        'ngn_rate_used' => 'decimal:8',
        'btc_usd_price' => 'decimal:2',
        'payment_data' => 'array',
        'expires_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_provider_check_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
