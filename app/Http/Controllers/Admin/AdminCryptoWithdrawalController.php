<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CryptoWithdrawal;
use App\Services\CryptoWithdrawalSettlementService;
use App\Models\AdminSetting;
use App\Services\NowPaymentsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AdminCryptoWithdrawalController extends Controller
{
    public function index()
    {
        $withdrawals = CryptoWithdrawal::with(['user', 'wallet'])->latest()->paginate(25);
        $nowPaymentsPayoutsEnabled = AdminSetting::getSetting('nowpayments_crypto_withdrawals_enabled', '0') === '1';
        return view('admin.crypto-withdrawals.index', compact('withdrawals', 'nowPaymentsPayoutsEnabled'));
    }

    public function createNowPaymentsPayout(CryptoWithdrawal $cryptoWithdrawal, NowPaymentsService $provider)
    {
        abort_unless(AdminSetting::getSetting('nowpayments_crypto_withdrawals_enabled', '0') === '1', 403, 'Crypto withdrawals are disabled in platform settings.');
        $withdrawal = DB::transaction(function () use ($cryptoWithdrawal) {
            $row = CryptoWithdrawal::whereKey($cryptoWithdrawal->id)->lockForUpdate()->firstOrFail();
            if ($row->status !== 'processing') throw ValidationException::withMessages(['payout' => 'Review and approve this request for processing before creating an external payout.']);
            if ($row->provider_payout_id || $row->provider_payout_started_at) throw ValidationException::withMessages(['payout' => 'A payout attempt already exists or may have reached NOWPayments. Refresh its status; do not create another payout.']);
            $row->provider_payout_started_at = now();
            $row->provider_payout_status = 'creating';
            $row->save();
            return $row;
        });
        try {
            $providerCode = $provider->payoutCurrencyCode((string) $withdrawal->wallet->currency, (string) $withdrawal->network);
            $available = $provider->custodyAvailableBalance($providerCode);
            if ($available + 0.00000001 < (float) $withdrawal->amount) {
                $withdrawal->update(['provider_payout_status' => 'insufficient_reserve', 'provider_payout_started_at' => null]);
                return back()->withErrors(['payout' => 'NOWPayments available balance is below the requested payout. No payout was created.']);
            }
            $provider->validatePayoutAddress($withdrawal->address, $providerCode);
        } catch (\Throwable $e) {
            $withdrawal->update(['provider_payout_status' => 'preflight_failed', 'provider_payout_started_at' => null]);
            Log::warning('NOWPayments crypto payout preflight failed before any payout create request.', ['crypto_withdrawal_id' => $withdrawal->id, 'exception' => $e->getMessage()]);
            return back()->withErrors(['payout' => 'NOWPayments could not validate the payout or reserve. No payout was created; correct the issue and retry.']);
        }
        try {
            $response = $provider->createCryptoPayout($withdrawal->address, $providerCode, (float) $withdrawal->amount, 'KayXchange CW-' . $withdrawal->id);
            $batchId = (string) ($response['id'] ?? $response['batch_withdrawal_id'] ?? $response['withdrawal_id'] ?? '');
            if ($batchId === '') {
                $withdrawal->update(['provider_payout_status' => 'unknown', 'provider_payout_response' => $response]);
                Log::critical('NOWPayments payout response omitted its batch ID; automatic retry is blocked.', ['crypto_withdrawal_id' => $withdrawal->id]);
                return back()->withErrors(['payout' => 'NOWPayments returned no payout ID. This request is locked for reconciliation; do not retry it. Check the provider dashboard and the admin logs.']);
            }
            $withdrawal->update(['provider_payout_id' => $batchId, 'provider_payout_status' => strtolower((string) ($response['status'] ?? 'created')), 'provider_payout_response' => $response]);
            return back()->with('success', 'NOWPayments payout batch ' . $batchId . ' was created. Enter the one-time provider verification code below to authorize the transfer.');
        } catch (\Throwable $e) {
            // A timed-out create call may still have created a remote payout.
            $withdrawal->update(['provider_payout_status' => 'unknown']);
            Log::error('NOWPayments crypto payout creation failed or returned an uncertain result.', ['crypto_withdrawal_id' => $withdrawal->id, 'exception' => $e->getMessage()]);
            return back()->withErrors(['payout' => 'The payout result could not be confirmed. It is locked against duplicate sending; reconcile it in NOWPayments and the Laravel log before retrying.']);
        }
    }

    public function verifyNowPaymentsPayout(Request $request, CryptoWithdrawal $cryptoWithdrawal, NowPaymentsService $provider)
    {
        abort_unless(AdminSetting::getSetting('nowpayments_crypto_withdrawals_enabled', '0') === '1', 403);
        $data = $request->validate(['verification_code' => ['required', 'digits:6']]);
        if (!$cryptoWithdrawal->provider_payout_id || $cryptoWithdrawal->provider_payout_verified_at) {
            return back()->withErrors(['payout' => 'No unverified payout batch is available for this request.']);
        }
        try {
            $result = $provider->verifyCryptoPayout($cryptoWithdrawal->provider_payout_id, $data['verification_code']);
            $cryptoWithdrawal->update([
                'provider_payout_verified_at' => now(),
                'provider_payout_status' => strtolower((string) ($result['status'] ?? data_get($result, 'withdrawals.0.status', 'verified'))),
                'provider_payout_response' => $result,
            ]);
            return back()->with('success', 'Payout verification was accepted. Refresh provider status before marking the customer request paid.');
        } catch (\Throwable $e) {
            Log::warning('NOWPayments payout 2FA verification failed.', ['crypto_withdrawal_id' => $cryptoWithdrawal->id, 'admin_id' => $request->user()->id, 'exception' => $e->getMessage()]);
            return back()->withErrors(['payout' => 'NOWPayments did not accept the verification code. Check the current code and try again.']);
        }
    }

    public function refreshNowPaymentsPayout(CryptoWithdrawal $cryptoWithdrawal, NowPaymentsService $provider, CryptoWithdrawalSettlementService $settlement)
    {
        if (!$cryptoWithdrawal->provider_payout_id) return back()->withErrors(['payout' => 'No NOWPayments payout ID is saved for this request.']);
        try {
            $result = $provider->cryptoPayoutStatus($cryptoWithdrawal->provider_payout_id);
            $status = strtolower((string) ($result['status'] ?? data_get($result, 'withdrawals.0.status', 'unknown')));
            $cryptoWithdrawal->update(['provider_payout_status' => $status, 'provider_payout_response' => $result]);
            if (in_array($status, ['finished', 'completed', 'success', 'successful'], true)) {
                $hash = (string) ($result['hash'] ?? $result['txid'] ?? $result['transaction_hash'] ?? data_get($result, 'withdrawals.0.hash', data_get($result, 'withdrawals.0.txid', '')));
                if ($hash !== '') $settlement->updateStatus($cryptoWithdrawal->id, 'completed', $hash);
                return back()->with('success', $hash !== '' ? 'NOWPayments confirms this payout finished; the customer withdrawal was marked complete.' : 'NOWPayments reports the payout finished. Add the confirmed transaction hash to finalize wallet settlement.');
            }
            if (in_array($status, ['rejected', 'failed', 'cancelled'], true)) {
                return back()->with('error', 'NOWPayments reports payout status: ' . $status . '. Review provider details before rejecting or resubmitting.');
            }
            return back()->with('success', 'Latest NOWPayments payout status: ' . $status . '.');
        } catch (\Throwable $e) {
            Log::warning('NOWPayments crypto payout status refresh failed.', ['crypto_withdrawal_id' => $cryptoWithdrawal->id, 'exception' => $e->getMessage()]);
            return back()->withErrors(['payout' => 'Could not refresh payout status. No wallet settlement was changed.']);
        }
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
