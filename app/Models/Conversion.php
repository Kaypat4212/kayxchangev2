<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Conversion extends Model
{
    protected $fillable = [
        'user_id',
        'from_coin',
        'to_coin',
        'from_amount',
        'to_amount',
        'fee_amount',
        'rate_used',
        'status',
        'mode',
        'wallet_address',
        'admin_note',
        'processed_by',
        'cryptomus_order_id',
        'cryptomus_response',
        'completed_at',
        'failure_reason',
    ];

    protected $casts = [
        'from_amount' => 'decimal:8',
        'to_amount' => 'decimal:8',
        'fee_amount' => 'decimal:8',
        'rate_used' => 'decimal:8',
        'cryptomus_response' => 'array',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function isManual(): bool
    {
        return $this->mode === 'manual';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
