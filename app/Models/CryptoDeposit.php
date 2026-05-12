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
        'status',
        'cryptomus_payment_id',
        'payment_url',
        'payment_address',
        'payment_data',
        'transaction_hash',
        'expires_at',
        'completed_at',
    ];

    protected $casts = [
        'expected_amount' => 'decimal:8',
        'received_amount' => 'decimal:8',
        'payment_data' => 'array',
        'expires_at' => 'datetime',
        'completed_at' => 'datetime',
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
