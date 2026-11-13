<?php

namespace App\Services;

use App\Models\AdminSetting;
use App\Models\Deposit;
use App\Models\SellTrade;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Applies NGN balance changes together with the transaction status that caused
 * them. Locking the transaction row makes webhook retries and concurrent admin
 * / Telegram approvals idempotent.
 */
class BalanceSettlementService
{
    public function creditNgnBalance(int $userId, float $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        DB::transaction(fn () => $this->changeNgnBalance($userId, round($amount, 2)));
    }

    public function adjustNgnBalance(int $userId, float $delta): void
    {
        DB::transaction(fn () => $this->changeNgnBalance($userId, round($delta, 2)));
    }

    public function setNgnBalance(int $userId, float $amount): void
    {
        if ($amount < 0) {
            throw new RuntimeException('NGN balance cannot be negative.');
        }

        DB::transaction(function () use ($userId, $amount): void {
            $user = User::query()->lockForUpdate()->findOrFail($userId);
            $this->changeNgnBalance($userId, round($amount - (float) $user->balance, 2));
        });
    }

    public function approveDeposit(int $depositId, ?string $gatewayResponse = null, ?string $adminNote = null): bool
    {
        return DB::transaction(function () use ($depositId, $gatewayResponse, $adminNote): bool {
            $deposit = Deposit::query()->lockForUpdate()->findOrFail($depositId);

            $currentStatus = strtolower((string) $deposit->status);
            if ($currentStatus !== 'pending') {
                return false;
            }

            $fee = $this->depositFee($deposit);
            $credit = max(0, round((float) $deposit->amount - $fee, 2));

            $deposit->status = 'approved';
            $deposit->fee_amount = $fee;
            if ($gatewayResponse !== null) {
                $deposit->gateway_response = $gatewayResponse;
            }
            if ($adminNote !== null) {
                $deposit->admin_note = $adminNote;
            }
            $deposit->save();

            $this->changeNgnBalance((int) $deposit->user_id, $credit);

            return true;
        });
    }

    public function setDepositStatus(
        int $depositId,
        string $status,
        ?string $adminNote = null,
        ?string $gatewayResponse = null
    ): bool
    {
        if ($status === 'approved') {
            return $this->approveDeposit($depositId, $gatewayResponse, $adminNote);
        }

        return DB::transaction(function () use ($depositId, $status, $adminNote, $gatewayResponse): bool {
            $deposit = Deposit::query()->lockForUpdate()->findOrFail($depositId);
            if (strtolower((string) $deposit->status) !== 'pending') {
                return false;
            }

            $deposit->status = $status;
            if ($adminNote !== null) {
                $deposit->admin_note = $adminNote;
            }
            if ($gatewayResponse !== null) {
                $deposit->gateway_response = $gatewayResponse;
            }
            $deposit->save();

            return true;
        });
    }

    public function updateSellStatus(int $tradeId, string $status, ?string $transactionReference = null): array
    {
        return DB::transaction(function () use ($tradeId, $status, $transactionReference): array {
            $trade = SellTrade::query()->lockForUpdate()->findOrFail($tradeId);
            $currentStatus = strtolower((string) $trade->status);
            if ($currentStatus === strtolower($status)) {
                return ['changed' => false, 'old_status' => $trade->status, 'trade' => $trade];
            }

            // A completed payout is final. Changing it back could cause a later
            // approval to credit the same sell a second time.
            if ($currentStatus === 'completed') {
                throw new RuntimeException('A completed sell trade cannot be reopened or reversed here.');
            }

            if ($status === 'completed' && $trade->payment_method === 'wallet_balance') {
                $this->changeNgnBalance((int) $trade->user_id, (float) $trade->naira_amount);
            }

            $oldStatus = $trade->status;
            $trade->status = $status;
            if ($transactionReference !== null) {
                $trade->transaction_ref = $transactionReference;
            }
            $trade->save();

            return ['changed' => true, 'old_status' => $oldStatus, 'trade' => $trade];
        });
    }

    public function approveWithdrawal(int $withdrawalId, string $approvedStatus = 'approved'): bool
    {
        return DB::transaction(function () use ($withdrawalId, $approvedStatus): bool {
            $withdrawal = Withdrawal::query()->lockForUpdate()->findOrFail($withdrawalId);
            if (in_array(strtolower((string) $withdrawal->status), ['approved', 'successful'], true)) {
                return false;
            }
            if (strtolower((string) $withdrawal->status) !== 'pending') {
                return false;
            }

            $totalDebit = round((float) $withdrawal->amount + (float) ($withdrawal->fee_amount ?? 0), 2);
            $this->changeNgnBalance((int) $withdrawal->user_id, -$totalDebit);

            $withdrawal->status = $approvedStatus;
            $withdrawal->processed_at = now();
            $withdrawal->save();

            return true;
        });
    }

    public function setWithdrawalStatus(int $withdrawalId, string $status): bool
    {
        if (in_array($status, ['approved', 'successful'], true)) {
            return $this->approveWithdrawal($withdrawalId, $status);
        }

        return DB::transaction(function () use ($withdrawalId, $status): bool {
            $withdrawal = Withdrawal::query()->lockForUpdate()->findOrFail($withdrawalId);
            if (strtolower((string) $withdrawal->status) === strtolower($status)) {
                return false;
            }
            if (strtolower((string) $withdrawal->status) !== 'pending') {
                throw new RuntimeException('Only pending withdrawals can be cancelled or changed.');
            }

            $withdrawal->status = $status;
            if (in_array($status, ['cancelled', 'canceled'], true)) {
                $withdrawal->processed_at = now();
            }
            $withdrawal->save();

            return true;
        });
    }

    private function depositFee(Deposit $deposit): float
    {
        $fee = (float) ($deposit->fee_amount ?? 0);
        if ($fee > 0) {
            return min($fee, (float) $deposit->amount);
        }

        $type = AdminSetting::getSetting('deposit_fee_type', 'none');
        $value = max(0, (float) AdminSetting::getSetting('deposit_fee_value', '0'));
        $fee = match ($type) {
            'flat' => $value,
            'percentage' => round((float) $deposit->amount * $value / 100, 2),
            default => 0.0,
        };

        return min(max(0, round($fee, 2)), (float) $deposit->amount);
    }

    private function changeNgnBalance(int $userId, float $delta): void
    {
        $user = User::query()->lockForUpdate()->findOrFail($userId);
        $newBalance = round((float) $user->balance + $delta, 2);

        if ($newBalance < 0) {
            throw new RuntimeException('Insufficient NGN balance, including withdrawal fees.');
        }

        $user->balance = $newBalance;
        $user->save();

        // Keep the legacy NGN wallet row aligned with users.balance. Trade and
        // withdrawal screens use users.balance as the canonical NGN balance.
        $wallet = Wallet::query()
            ->where('user_id', $userId)
            ->where('currency', 'NGN')
            ->lockForUpdate()
            ->first();

        if (!$wallet) {
            $wallet = new Wallet(['user_id' => $userId, 'currency' => 'NGN']);
        }

        $wallet->balance = $newBalance;
        $wallet->save();
    }
}
