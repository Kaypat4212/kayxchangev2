<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\WalletLedgerEntry;

class WalletLedgerService
{
    public function record(Wallet $wallet, float $delta, string $source, ?string $reference = null, array $metadata = []): void
    {
        if (abs($delta) < 0.000000005) return;

        WalletLedgerEntry::create([
            'user_id' => $wallet->user_id,
            'wallet_id' => $wallet->id,
            'currency' => strtoupper((string) $wallet->currency),
            'entry_type' => $delta > 0 ? 'credit' : 'debit',
            'amount' => abs($delta),
            'balance_after' => (float) $wallet->balance,
            'reference' => $reference,
            'source' => $source,
            'metadata' => $metadata ?: null,
        ]);
    }
}
