<?php

namespace App\Services;

use App\Models\CryptoWithdrawal;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CryptoWithdrawalSettlementService
{
    /** Update a manual crypto payout once; debit the asset only when transfer is confirmed. */
    public function updateStatus(int $id, string $status, ?string $transactionHash = null, ?string $reason = null): bool
    {
        return DB::transaction(function () use ($id, $status, $transactionHash, $reason) {
            $withdrawal = CryptoWithdrawal::whereKey($id)->lockForUpdate()->firstOrFail();
            if ($withdrawal->status === $status) return false;
            if (in_array($withdrawal->status, ['completed', 'rejected'], true)) {
                throw ValidationException::withMessages(['status' => 'This withdrawal is already finalized.']);
            }
            if (!in_array($status, ['processing', 'completed', 'rejected'], true)) {
                throw ValidationException::withMessages(['status' => 'Invalid withdrawal status.']);
            }
            if ($status === 'completed' && !$transactionHash) {
                throw ValidationException::withMessages(['transaction_hash' => 'A transaction hash is required to complete the payout.']);
            }

            if ($status === 'completed') {
                $wallet = $withdrawal->wallet()->lockForUpdate()->firstOrFail();
                $debit = (float) $withdrawal->amount + (float) $withdrawal->fee;
                if ((float) $wallet->balance < $debit) {
                    throw ValidationException::withMessages(['status' => 'Wallet balance is no longer sufficient to settle this withdrawal.']);
                }
                $wallet->decrement('balance', $debit);
                $withdrawal->transaction_hash = $transactionHash;
                $withdrawal->processed_at = now();
            }

            $withdrawal->status = $status;
            if ($status === 'rejected') $withdrawal->rejected_reason = $reason;
            $withdrawal->save();
            return true;
        });
    }
}
