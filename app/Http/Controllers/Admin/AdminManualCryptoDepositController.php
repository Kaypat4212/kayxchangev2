<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CryptoDeposit;
use App\Services\CryptoWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminManualCryptoDepositController extends Controller
{
    public function index()
    {
        $deposits = CryptoDeposit::with('user')->latest()->paginate(25);
        $reviewCount = CryptoDeposit::where('status', 'awaiting_admin_review')->count()
            + CryptoDeposit::where('status', 'pending')->where('payment_data->provider', 'manual')->count();
        return view('admin.manual-crypto-deposits', compact('deposits', 'reviewCount'));
    }

    public function approve(Request $request, int $deposit, CryptoWalletService $wallets)
    {
        $data = $request->validate([
            'received_amount'=>'nullable|numeric|gt:0|max:1000000000',
            'transaction_hash'=>'nullable|string|min:20|max:150|regex:/^[A-Za-z0-9_-]+$/',
        ]);
        $record = CryptoDeposit::findOrFail($deposit);
        $isManual = data_get($record->payment_data, 'provider') === 'manual';
        if (!$isManual && data_get($record->payment_data, 'provider') === 'nowpayments') {
            try {
                $record = $wallets->refreshDepositStatus($record, true);
            } catch (\Throwable $exception) {
                \Illuminate\Support\Facades\Log::warning('Admin could not refresh NOWPayments deposit before approval.', [
                    'deposit_id' => $record->id,
                    'error' => $exception->getMessage(),
                ]);
                return back()->with('error', 'NOWPayments could not be reached to verify the latest status. No balance was credited. Retry shortly.');
            }
        }
        if (($isManual && $record->status !== 'pending') || (!$isManual && $record->status !== 'awaiting_admin_review')) {
            return back()->with('error', 'This deposit is no longer awaiting admin review. Refresh the queue to see its latest status.');
        }
        if (!$isManual) {
            $paymentData = $record->payment_data ?? [];
            $providerStatus = strtolower((string)($paymentData['provider_status'] ?? ''));
            $receivedCrypto = (float)($record->received_amount ?? 0);
            if (data_get($paymentData, 'provider') === 'nowpayments'
                && ($providerStatus !== 'finished' || $receivedCrypto <= 0)) {
                return back()->with('error', 'NOWPayments has not confirmed a completed payment with a received amount yet. No balance was credited.');
            }
            $paymentData['admin_review_approved'] = true;
            $paymentData['admin_reviewed_by'] = auth()->id();
            $paymentData['admin_reviewed_at'] = now()->toIso8601String();
            $record->payment_data = $paymentData;
            $record->save();
            $wallets->processDepositSuccess((array)data_get($paymentData, 'latest_webhook', []));
            return $record->fresh()->status === 'completed'
                ? back()->with('success', 'Confirmed crypto deposit approved and wallet balance credited.')
                : back()->with('error', 'Deposit could not be credited. Check the provider record and try again.');
        }
        if (empty($data['received_amount']) || empty($data['transaction_hash'])) {
            return back()->withErrors(['received_amount'=>'Verified amount and transaction hash are required for a manual deposit.']);
        }
        $wallets->processDepositSuccess([
            'order_id'=>'dep_' . $record->id,
            '_provider'=>'manual',
            'payment_status'=>'paid',
            'actually_paid'=>(float)$data['received_amount'],
            'pay_currency'=>(string)data_get($record->payment_data, 'provider_pay_currency', strtolower($record->currency)),
            'price_amount'=>(float)$record->expected_amount,
            'txid'=>$data['transaction_hash'],
            'manual_review'=>true,
        ]);
        if ($record->fresh()->status !== 'completed') return back()->with('error', 'The deposit could not be settled. Confirm it is still pending and retry.');
        return back()->with('success', 'Manual crypto deposit verified and wallet balance credited.');
    }

    public function reject(Request $request, int $deposit)
    {
        $data = $request->validate(['admin_note'=>'nullable|string|max:500']);
        $changed = DB::transaction(function () use ($deposit, $data): bool {
            $record = CryptoDeposit::query()->lockForUpdate()->findOrFail($deposit);
            $isManual = data_get($record->payment_data, 'provider') === 'manual';
            if (($isManual && $record->status !== 'pending') || (!$isManual && $record->status !== 'awaiting_admin_review')) return false;
            $paymentData = $record->payment_data ?? [];
            $paymentData['provider_status'] = 'rejected_by_admin';
            $paymentData['manual_review_note'] = trim((string)($data['admin_note'] ?? ''));
            $paymentData['admin_reviewed_by'] = auth()->id();
            $paymentData['admin_reviewed_at'] = now()->toIso8601String();
            $paymentData['manual_reviewed_by'] = auth()->id();
            $paymentData['manual_reviewed_at'] = now()->toIso8601String();
            $record->payment_data = $paymentData;
            $record->status = 'failed';
            $record->save();
            return true;
        });
        return back()->with($changed ? 'success' : 'error', $changed ? 'Fallback deposit rejected; no balance was credited.' : 'This fallback deposit is no longer awaiting review.');
    }
}
