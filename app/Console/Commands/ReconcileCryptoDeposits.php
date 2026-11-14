<?php

namespace App\Console\Commands;

use App\Models\CryptoDeposit;
use App\Services\CryptoWalletService;
use App\Services\NowPaymentsService;
use App\Models\User;
use App\Models\Notification;
use App\Models\Deposit;
use App\Models\Withdrawal;
use App\Models\CryptoWithdrawal;
use App\Models\BillPayment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ReconcileCryptoDeposits extends Command
{
    protected $signature = 'payments:reconcile-deposits {--limit=20}';
    protected $description = 'Recheck pending crypto payment invoices with their provider';

    public function handle(NowPaymentsService $nowPayments, CryptoWalletService $wallets): int
    {
        Cache::put('provider_health.deposit_reconciliation.last_run_at', now()->toIso8601String(), now()->addDays(7));
        $checked = 0;
        $updated = 0;
        $errors = 0;
        $deposits = CryptoDeposit::query()
            ->where(function ($query) {
                $query->whereIn('status', ['pending', 'awaiting_admin_review'])
                    ->orWhere(function ($late) {
                        $late->where('status', 'expired')->where('payment_data->local_window_expired', true);
                    });
            })
            ->where('created_at', '>=', now()->subDays(7))
            ->where(function ($query) {
                $query->whereNull('last_provider_check_at')
                    ->orWhere('last_provider_check_at', '<=', now()->subMinutes(3));
            })
            ->orderByRaw('last_provider_check_at IS NOT NULL')
            ->orderBy('last_provider_check_at')
            ->limit(max(1, min(100, (int) $this->option('limit'))))
            ->get();

        foreach ($deposits as $deposit) {
            $data = $deposit->payment_data ?? [];
            $provider = strtolower((string) ($data['provider'] ?? ''));
            if ($provider === 'nowpayments' && empty($data['provider_payment_id'])) continue;
            if (!in_array($provider, ['nowpayments', 'cryptomus'], true)) continue;
            $checked++;
            $deposit->forceFill(['last_provider_check_at' => now()])->save();
            try {
                $before = $deposit->status;
                if ($provider === 'nowpayments') {
                    $providerData = $nowPayments->paymentStatus((string) $data['provider_payment_id']);
                    $providerData['order_id'] = $providerData['order_id'] ?? 'dep_' . $deposit->id;
                    $providerData['_provider'] = 'nowpayments';
                    $wallets->processDepositSuccess($providerData);
                    $fresh = $deposit->fresh();
                    if ($fresh && $fresh->status === 'pending' && $fresh->expires_at && $fresh->expires_at->isPast()
                        && in_array(strtolower((string)($providerData['payment_status'] ?? '')), ['waiting', 'partially_paid'], true)) {
                        $metadata = $fresh->payment_data ?? [];
                        $metadata['local_window_expired'] = true;
                        $metadata['local_window_expired_at'] = now()->toIso8601String();
                        $metadata['provider_reported_status'] = $providerData['payment_status'] ?? 'waiting';
                        $metadata['provider_status'] = 'local_window_expired';
                        $fresh->payment_data = $metadata;
                        $fresh->status = 'expired';
                        $fresh->save();
                    }
                    Cache::put('provider_health.nowpayments.last_success_at', now()->toIso8601String(), now()->addDays(7));
                    Cache::put('provider_health.nowpayments.last_error', null, now()->addDays(7));
                } else {
                    $wallets->refreshDepositStatus($deposit);
                }
                if ($deposit->fresh()->status !== $before) $updated++;
            } catch (\Throwable $e) {
                $errors++;
                if ($provider === 'nowpayments') {
                    $errorMessage = mb_substr($e->getMessage(), 0, 240);
                    Cache::put('provider_health.nowpayments.last_error', ['at' => now()->toIso8601String(), 'message' => $errorMessage], now()->addDays(7));
                    $this->notifyAdminsOnce('provider:' . sha1($errorMessage), 'Payment provider status check failed', 'NOWPayments status could not be refreshed. Review Operations Health and server logs.', ['provider' => 'nowpayments']);
                }
                Log::warning('Scheduled crypto deposit reconciliation failed', ['provider' => $provider, 'deposit_id' => $deposit->id, 'error' => $e->getMessage()]);
            }
        }

        $this->alertStaleRequests();

        $this->info("Reconciliation finished: {$checked} checked, {$updated} status changes, {$errors} provider errors.");
        Cache::put('provider_health.deposit_reconciliation.last_summary', compact('checked', 'updated', 'errors'), now()->addDays(7));
        return self::SUCCESS;
    }

    private function alertStaleRequests(): void
    {
        $cutoff = now()->subMinutes(30);
        foreach ([
            ['crypto_deposit', CryptoDeposit::class, ['pending', 'awaiting_admin_review']],
            ['deposit', Deposit::class, ['pending']],
            ['withdrawal', Withdrawal::class, ['pending', 'processing']],
            ['crypto_withdrawal', CryptoWithdrawal::class, ['pending', 'processing']],
            ['swap', \App\Models\Conversion::class, ['pending', 'processing']],
            ['bill_payment', BillPayment::class, ['pending', 'processing']],
        ] as [$type, $model, $statuses]) {
            foreach ($model::query()->whereIn('status', $statuses)->where('created_at', '<=', $cutoff)->latest()->limit(50)->get() as $request) {
                $this->notifyAdminsOnce('stale:' . $type . ':' . $request->getKey() . ':' . $request->status,
                    'Transaction needs review', ucfirst(str_replace('_', ' ', $type)) . ' #' . $request->getKey() . ' has remained ' . $request->status . ' for more than 30 minutes.',
                    ['transaction_type' => $type, 'transaction_id' => $request->getKey(), 'status' => $request->status]);
            }
        }
    }

    private function notifyAdminsOnce(string $key, string $title, string $message, array $data = []): void
    {
        if (!Cache::add('ops_notification_' . sha1($key), true, now()->addHours(24))) return;
        foreach (User::query()->where('is_admin', true)->get(['id']) as $admin) {
            Notification::create([
                'admin_id' => $admin->id, 'type' => 'warning', 'title' => $title, 'message' => $message,
                'data' => array_merge($data, ['alert_key' => $key]), 'is_read' => false,
            ]);
        }
    }
}
