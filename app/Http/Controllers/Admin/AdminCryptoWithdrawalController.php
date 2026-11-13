<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CryptoWithdrawal;
use App\Services\CryptoWithdrawalSettlementService;
use Illuminate\Http\Request;

class AdminCryptoWithdrawalController extends Controller
{
    public function index()
    {
        $withdrawals = CryptoWithdrawal::with(['user', 'wallet'])->latest()->paginate(25);
        return view('admin.crypto-withdrawals.index', compact('withdrawals'));
    }

    public function update(Request $request, CryptoWithdrawal $cryptoWithdrawal, CryptoWithdrawalSettlementService $settlement)
    {
        $oldStatus = $cryptoWithdrawal->status;
        $data = $request->validate([
            'status' => 'required|in:processing,completed,rejected',
            'transaction_hash' => 'nullable|string|max:255|required_if:status,completed',
            'rejected_reason' => 'nullable|string|max:1000|required_if:status,rejected',
        ]);
        $changed = $settlement->updateStatus(
            $cryptoWithdrawal->id,
            $data['status'],
            $data['transaction_hash'] ?? null,
            $data['rejected_reason'] ?? null
        );

        if ($changed) {
            $cryptoWithdrawal->refresh()->loadMissing(['user', 'wallet']);
            try {
                app(\App\Services\AdminTradeAlertService::class)->sendStatusChangeAlert('crypto withdrawal', [
                    'reference' => 'CW-' . $cryptoWithdrawal->id,
                    'user_name' => $cryptoWithdrawal->user?->name,
                    'old_status' => $oldStatus,
                    'new_status' => $cryptoWithdrawal->status,
                ]);
            } catch (\Throwable $e) { report($e); }
        }

        return back()->with('success', $changed ? 'Crypto withdrawal updated.' : 'No change was needed.');
    }
}
