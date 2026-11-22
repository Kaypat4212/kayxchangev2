<?php

namespace App\Services;

use App\Mail\TradeNotification;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class WithdrawalNotificationService
{
    public function approved(Withdrawal $withdrawal): void
    {
        try {
            $user = $withdrawal->user;
        } catch (\Throwable $e) {
            Log::warning('Withdrawal approval notification could not load the user.', ['withdrawal_id' => $withdrawal->id, 'error' => $e->getMessage()]);
            return;
        }
        if (!$user) {
            Log::warning('Withdrawal approval notification skipped: user not found.', ['withdrawal_id' => $withdrawal->id]);
            return;
        }

        $bank = is_array($withdrawal->bank_account)
            ? $withdrawal->bank_account
            : (json_decode((string) $withdrawal->bank_account, true) ?: []);
        $accountDetails = ($bank['account_name'] ?? 'N/A') . ' — '
            . ($bank['account_number'] ?? 'N/A') . ' (' . ($bank['bank_name'] ?? 'N/A') . ')';
        $amount = number_format((float) $withdrawal->amount, 2);

        try {
            if ($user->email) {
                Mail::to($user->email)->send(new TradeNotification(
                    user: $user,
                    templateKey: 'withdrawal_approved',
                    data: [
                        'amount' => $amount,
                        'currency' => 'NGN',
                        'payment_method' => 'Naira bank transfer',
                        'account_details' => $accountDetails,
                        'reference' => $withdrawal->reference,
                    ],
                    badge: ['text' => '₦' . $amount . ' sent to bank', 'color' => '#00cc00'],
                    ctaUrl: url('/dashboard'),
                    ctaText: 'View Dashboard',
                ));
            }
        } catch (\Throwable $e) {
            Log::warning('Withdrawal approved email notification failed.', ['withdrawal_id' => $withdrawal->id, 'error' => $e->getMessage()]);
        }

        try {
            app(TelegramService::class)->notifyWithdrawal($user, $withdrawal);
        } catch (\Throwable $e) {
            Log::warning('Withdrawal approved Telegram notification failed.', ['withdrawal_id' => $withdrawal->id, 'error' => $e->getMessage()]);
        }
    }
}
