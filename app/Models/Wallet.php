<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wallet extends Model
{
    protected $table = 'wallets';
    protected $fillable = [
        'user_id', 'balance', 'currency', 'address', 'network', 'is_active',
    ];

    protected $casts = [
        'balance' => 'decimal:8',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Helper methods
    public function isCrypto(): bool
    {
        return !in_array($this->currency, ['NGN', 'USD', 'EUR']);
    }

    public function getFormattedBalanceAttribute(): string
    {
        if ($this->isCrypto()) {
            return number_format($this->balance, 8, '.', '');
        }
        return number_format($this->balance, 2, '.', ',');
    }

    public function credit(float $amount): bool
    {
        $this->increment('balance', $amount);
        return true;
    }

    public function debit(float $amount): bool
    {
        if ($this->balance < $amount) {
            return false;
        }
        $this->decrement('balance', $amount);
        return true;
    }
}