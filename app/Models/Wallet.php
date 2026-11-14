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

    public function credit(float $amount, string $source = 'wallet.credit', ?string $reference = null): bool
    {
        if ($amount <= 0) return false;
        if (strtoupper((string) $this->currency) === 'NGN') {
            app(\App\Services\BalanceSettlementService::class)->adjustNgnBalance((int) $this->user_id, $amount, $source, $reference);
            $this->refresh();
            return true;
        }
        \Illuminate\Support\Facades\DB::transaction(function () use ($amount, $source, $reference): void {
            $wallet = static::query()->lockForUpdate()->findOrFail($this->getKey());
            $wallet->balance = (float) $wallet->balance + $amount;
            $wallet->save();
            app(\App\Services\WalletLedgerService::class)->record($wallet, $amount, $source, $reference);
            $this->setRawAttributes($wallet->getAttributes(), true);
        });
        return true;
    }

    public function debit(float $amount, string $source = 'wallet.debit', ?string $reference = null): bool
    {
        if ($amount <= 0) return false;
        if (strtoupper((string) $this->currency) === 'NGN') {
            try {
                app(\App\Services\BalanceSettlementService::class)->adjustNgnBalance((int) $this->user_id, -$amount, $source, $reference);
                $this->refresh();
                return true;
            } catch (\RuntimeException) {
                return false;
            }
        }
        return \Illuminate\Support\Facades\DB::transaction(function () use ($amount, $source, $reference): bool {
            $wallet = static::query()->lockForUpdate()->findOrFail($this->getKey());
            if ((float) $wallet->balance < $amount) return false;
            $wallet->balance = (float) $wallet->balance - $amount;
            $wallet->save();
            app(\App\Services\WalletLedgerService::class)->record($wallet, -$amount, $source, $reference);
            $this->setRawAttributes($wallet->getAttributes(), true);
            return true;
        });
    }
}
