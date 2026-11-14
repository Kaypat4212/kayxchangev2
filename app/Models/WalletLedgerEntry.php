<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletLedgerEntry extends Model
{
    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Wallet ledger entries are immutable; post a correcting entry instead.'));
        static::deleting(fn () => throw new \LogicException('Wallet ledger entries are immutable and cannot be deleted.'));
    }

    protected $fillable = [
        'user_id', 'wallet_id', 'currency', 'entry_type', 'amount',
        'balance_after', 'reference', 'source', 'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:8',
        'balance_after' => 'decimal:8',
        'metadata' => 'array',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function wallet() { return $this->belongsTo(Wallet::class); }
}
