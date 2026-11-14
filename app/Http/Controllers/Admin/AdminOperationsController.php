<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\BillPayment;
use App\Models\Conversion;
use App\Models\CryptoDeposit;
use App\Models\CryptoWithdrawal;
use App\Models\Deposit;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\Cache;

class AdminOperationsController extends Controller
{
    public function index()
    {
        $cutoff = now()->subMinutes(30);
        $stuck = collect();
        foreach ([
            ['Crypto deposit', CryptoDeposit::class, ['pending', 'awaiting_admin_review']],
            ['NGN deposit', Deposit::class, ['pending']],
            ['NGN withdrawal', Withdrawal::class, ['pending', 'processing']],
            ['Crypto withdrawal', CryptoWithdrawal::class, ['pending', 'processing']],
            ['Swap', Conversion::class, ['pending', 'processing']],
            ['Bill payment', BillPayment::class, ['pending', 'processing']],
        ] as [$label, $model, $statuses]) {
            $stuck = $stuck->merge($model::query()->with('user')->whereIn('status', $statuses)
                ->where('created_at', '<', $cutoff)->latest()->limit(25)->get()
                ->map(fn ($row) => ['type' => $label, 'id' => $row->getKey(), 'reference' => $row->reference ?? $row->transaction_ref ?? ('#' . $row->getKey()), 'user' => $row->user?->name ?? 'Unknown user', 'status' => $row->status, 'created_at' => $row->created_at]));
        }

        $providerHealth = [
            'reconciliation_last_run' => Cache::get('provider_health.deposit_reconciliation.last_run_at'),
            'reconciliation_summary' => Cache::get('provider_health.deposit_reconciliation.last_summary'),
            'nowpayments_last_success' => Cache::get('provider_health.nowpayments.last_success_at'),
            'nowpayments_last_error' => Cache::get('provider_health.nowpayments.last_error'),
        ];
        $ledger = \App\Models\WalletLedgerEntry::with('user')->latest()->paginate(20, ['*'], 'ledger_page');
        $auditLogs = AdminAuditLog::with('admin')->latest()->paginate(20, ['*'], 'audit_page');
        return view('admin.operations', compact('stuck', 'providerHealth', 'ledger', 'auditLogs'));
    }

    public function audit()
    {
        $logs = AdminAuditLog::with('admin')->latest()->paginate(50);
        return view('admin.audit-log', compact('logs'));
    }
}
