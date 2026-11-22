<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillPayment;
use App\Models\BuyTrade;
use App\Models\CryptoDeposit;
use App\Models\CryptoWithdrawal;
use App\Models\Deposit;
use App\Models\P2pTransfer;
use App\Models\SellTrade;
use App\Models\Withdrawal;
use App\Models\PortfolioWithdrawal;
use App\Models\Conversion;
use App\Models\GiftCardOrder;
use App\Models\GiftCardSaleOrder;
use App\Models\AdminAuditLog;
use App\Services\BalanceSettlementService;
use App\Services\WalletLedgerService;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class AdminTransactionHistoryController extends Controller
{
    public function update(Request $request, string $type, int $id)
    {
        abort_unless(in_array($type, ['deposit', 'withdrawal', 'sell', 'buy', 'crypto_withdrawal', 'crypto_swap'], true), 404);
        if ($type === 'crypto_withdrawal') return $this->updateCryptoWithdrawal($request, $id);
        if ($type === 'crypto_swap') return $this->updateCryptoSwap($request, $id);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:100000000000'],
            'fee_amount' => ['nullable', 'numeric', 'min:0', 'max:100000000000'],
            'status' => ['required', 'in:pending,approved,successful,completed,rejected,failed,cancelled'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);
        $editResult = DB::transaction(function () use ($request, $type, $id, $data) {
            $model = match ($type) { 'deposit' => Deposit::class, 'withdrawal' => Withdrawal::class, 'sell' => SellTrade::class, 'buy' => BuyTrade::class };
            $record = $model::query()->lockForUpdate()->findOrFail($id);
            $before = $record->only(['amount', 'fee_amount', 'naira_amount', 'status']);
            $oldImpact = $this->ngnImpact($type, $record);
            if (in_array($type, ['sell', 'buy'], true)) $record->naira_amount = round((float) $data['amount'], 2);
            else {
                $record->amount = round((float) $data['amount'], 2);
                if (array_key_exists('fee_amount', $data) && $data['fee_amount'] !== null) $record->fee_amount = round((float) $data['fee_amount'], 2);
            }
            $record->status = $data['status'];
            if ($type === 'withdrawal' && in_array($data['status'], ['approved', 'successful', 'completed'], true) && ! $record->processed_at) $record->processed_at = now();
            $newImpact = $this->ngnImpact($type, $record);
            $balanceDelta = round($newImpact - $oldImpact, 2);
            if (abs($balanceDelta) >= 0.01) {
                app(BalanceSettlementService::class)->adjustNgnBalance((int) $record->user_id, $balanceDelta, 'admin.transaction_edit.' . $type, $record->transaction_ref ?? $record->reference ?? ('transaction:' . $record->id));
            }
            $record->save();
            AdminAuditLog::create([
                'admin_id' => (int) $request->user()->id, 'action' => 'transaction.direct_edit',
                'subject_type' => $model, 'subject_id' => $record->id, 'before' => $before,
                'after' => $record->only(['amount', 'fee_amount', 'naira_amount', 'status']) + ['reason' => $data['reason'], 'ngn_balance_delta' => $balanceDelta],
                'ip_address' => $request->ip(), 'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            ]);
            return ['delta' => $balanceDelta, 'old_status' => strtolower((string) $before['status'])];
        });
        if ($type === 'withdrawal'
            && !in_array($editResult['old_status'], ['approved', 'successful', 'completed'], true)
            && in_array(strtolower((string) $data['status']), ['approved', 'successful', 'completed'], true)) {
            try {
                $withdrawal = Withdrawal::with('user')->find($id);
                if ($withdrawal) app(\App\Services\WithdrawalNotificationService::class)->approved($withdrawal);
            } catch (\Throwable $notificationError) {
                \Illuminate\Support\Facades\Log::warning('Approved withdrawal notification follow-up failed after transaction edit.', [
                    'withdrawal_id' => $id,
                    'error' => $notificationError->getMessage(),
                ]);
            }
        }
        return back()->with('success', 'Transaction updated directly. Balance adjustment: ₦' . number_format($editResult['delta'], 2) . '.');
    }

    private function updateCryptoWithdrawal(Request $request, int $id)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0', 'max:1000000000'],
            'fee' => ['nullable', 'numeric', 'min:0', 'max:1000000000'],
            'status' => ['required', 'in:pending,processing,completed,rejected,failed,cancelled'],
            'address' => ['required', 'string', 'max:255'],
            'network' => ['required', 'string', 'max:80'],
            'transaction_hash' => ['nullable', 'string', 'max:255', 'required_if:status,completed'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        DB::transaction(function () use ($request, $id, $data): void {
            $record = CryptoWithdrawal::query()->lockForUpdate()->findOrFail($id);
            $oldStatus = strtolower((string) $record->status);
            $newStatus = strtolower((string) $data['status']);
            if ($oldStatus === 'completed' && $newStatus !== 'completed') {
                throw ValidationException::withMessages(['status' => 'A completed crypto payout cannot be reopened or reversed here. Correct its transaction details while keeping it completed.']);
            }

            $before = $record->only(['amount', 'fee', 'address', 'network', 'status', 'transaction_hash']);
            $oldDebit = $oldStatus === 'completed' ? (float) $record->amount + (float) $record->fee : 0.0;
            $newAmount = round((float) $data['amount'], 8);
            $newFee = round((float) ($data['fee'] ?? $record->fee ?? 0), 8);
            $newDebit = $newStatus === 'completed' ? $newAmount + $newFee : 0.0;
            $walletDelta = round($oldDebit - $newDebit, 8);

            if (abs($walletDelta) >= 0.000000005) {
                $wallet = Wallet::query()->whereKey($record->wallet_id)->where('user_id', $record->user_id)->lockForUpdate()->firstOrFail();
                $adjustedBalance = round((float) $wallet->balance + $walletDelta, 8);
                if ($adjustedBalance < -0.000000005) {
                    throw ValidationException::withMessages(['amount' => 'This correction would make the user’s crypto wallet balance negative.']);
                }
                $wallet->balance = max(0, $adjustedBalance);
                $wallet->save();
                app(WalletLedgerService::class)->record($wallet, $walletDelta, 'crypto_withdrawal.admin_correction', 'crypto-withdrawal:' . $record->id, [
                    'admin_id' => $request->user()->id,
                    'reason' => trim($data['reason']),
                    'previous_debit' => $oldDebit,
                    'updated_debit' => $newDebit,
                ]);
            }

            $record->amount = $newAmount;
            $record->fee = $newFee;
            $record->address = trim($data['address']);
            $record->network = trim($data['network']);
            $record->status = $newStatus;
            $record->transaction_hash = filled($data['transaction_hash'] ?? null) ? trim($data['transaction_hash']) : null;
            if ($newStatus === 'completed' && !$record->processed_at) $record->processed_at = now();
            $record->save();

            $this->auditDirectEdit($request, CryptoWithdrawal::class, $record->id, $before,
                $record->only(['amount', 'fee', 'address', 'network', 'status', 'transaction_hash']) + ['reason' => trim($data['reason']), 'crypto_wallet_delta' => $walletDelta]);
        });

        return back()->with('success', 'Crypto withdrawal updated. Any completed-payout balance correction was recorded in the crypto wallet ledger.');
    }

    private function updateCryptoSwap(Request $request, int $id)
    {
        $data = $request->validate([
            'from_amount' => ['required', 'numeric', 'gt:0', 'max:1000000000'],
            'to_amount' => ['required', 'numeric', 'min:0', 'max:1000000000'],
            'fee_amount' => ['nullable', 'numeric', 'min:0', 'max:1000000000'],
            'status' => ['required', 'in:pending,processing,completed,failed'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        DB::transaction(function () use ($request, $id, $data): void {
            $record = Conversion::query()->where('mode', 'nowpayments')->lockForUpdate()->findOrFail($id);
            $oldStatus = strtolower((string) $record->status);
            $newStatus = strtolower((string) $data['status']);
            if (in_array($oldStatus, ['completed', 'failed'], true) && $newStatus !== $oldStatus) {
                throw ValidationException::withMessages(['status' => 'A finalized provider swap cannot be reopened or reversed here. Correct its amounts while keeping its final status.']);
            }
            if ($newStatus === 'completed' && (float) $data['to_amount'] <= 0) {
                throw ValidationException::withMessages(['to_amount' => 'A completed swap must have a positive receive amount.']);
            }

            $before = $record->only(['from_coin', 'to_coin', 'from_amount', 'to_amount', 'fee_amount', 'status', 'provider_status']);
            $oldSourceDebit = $oldStatus === 'failed' ? 0.0 : (float) $record->from_amount;
            $newFromAmount = round((float) $data['from_amount'], 8);
            $newToAmount = round((float) $data['to_amount'], 8);
            $newSourceDebit = $newStatus === 'failed' ? 0.0 : $newFromAmount;
            $oldDestinationCredit = $oldStatus === 'completed' ? (float) $record->to_amount : 0.0;
            $newDestinationCredit = $newStatus === 'completed' ? $newToAmount : 0.0;

            $walletDeltas = [];
            $fromCoin = strtoupper((string) $record->from_coin);
            $toCoin = strtoupper((string) $record->to_coin);
            $walletDeltas[$fromCoin] = ($oldSourceDebit - $newSourceDebit);
            $walletDeltas[$toCoin] = ($walletDeltas[$toCoin] ?? 0.0) + ($newDestinationCredit - $oldDestinationCredit);
            $wallets = Wallet::query()->where('user_id', $record->user_id)->whereIn('currency', array_keys($walletDeltas))->orderBy('currency')->lockForUpdate()->get()->keyBy(fn (Wallet $wallet) => strtoupper((string) $wallet->currency));
            foreach ($walletDeltas as $coin => $delta) {
                if (abs($delta) < 0.000000005) continue;
                $wallet = $wallets->get($coin);
                if (!$wallet) throw ValidationException::withMessages(['status' => "The user’s {$coin} wallet is missing; no swap edit was applied."]);
                $adjustedBalance = round((float) $wallet->balance + $delta, 8);
                if ($adjustedBalance < -0.000000005) throw ValidationException::withMessages(['status' => "This correction would make the user’s {$coin} wallet balance negative."]);
                $wallet->balance = max(0, $adjustedBalance);
                $wallet->save();
                app(WalletLedgerService::class)->record($wallet, $delta, 'crypto_swap.admin_correction', 'conversion:' . $record->id, [
                    'admin_id' => $request->user()->id,
                    'reason' => trim($data['reason']),
                    'previous_status' => $oldStatus,
                    'updated_status' => $newStatus,
                ]);
            }

            $record->from_amount = $newFromAmount;
            $record->to_amount = $newToAmount;
            $record->fee_amount = round((float) ($data['fee_amount'] ?? $record->fee_amount ?? 0), 8);
            $record->status = $newStatus;
            if ($newStatus === 'completed' && !$record->completed_at) $record->completed_at = now();
            if ($newStatus === 'failed' && !$record->failure_reason) $record->failure_reason = trim($data['reason']);
            $record->admin_note = trim($data['reason']);
            $record->save();

            $this->auditDirectEdit($request, Conversion::class, $record->id, $before,
                $record->only(['from_coin', 'to_coin', 'from_amount', 'to_amount', 'fee_amount', 'status', 'provider_status']) + ['reason' => trim($data['reason']), 'wallet_deltas' => $walletDeltas]);
        });

        return back()->with('success', 'Crypto swap updated. Any balance correction was applied to the relevant crypto wallet ledgers.');
    }

    private function auditDirectEdit(Request $request, string $model, int $id, array $before, array $after): void
    {
        AdminAuditLog::create([
            'admin_id' => (int) $request->user()->id,
            'action' => 'transaction.direct_edit',
            'subject_type' => $model,
            'subject_id' => $id,
            'before' => $before,
            'after' => $after,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);
    }

    private function ngnImpact(string $type, $record): float
    {
        $status = strtolower((string) $record->status);
        if ($type === 'deposit') return $status === 'approved' ? max(0, (float) $record->amount - (float) ($record->fee_amount ?? 0)) : 0.0;
        if ($type === 'withdrawal') return in_array($status, ['approved', 'successful', 'completed'], true) ? -((float) $record->amount + (float) ($record->fee_amount ?? 0)) : 0.0;
        return $status === 'completed' && $record->payment_method === 'wallet_balance' ? (float) $record->naira_amount : 0.0;
    }

    public function index(Request $request)
    {
        $types = ['buy'=>BuyTrade::class, 'sell'=>SellTrade::class, 'deposit'=>Deposit::class,
            'withdrawal'=>Withdrawal::class, 'crypto_deposit'=>CryptoDeposit::class,
            'crypto_withdrawal'=>CryptoWithdrawal::class, 'bill_payment'=>BillPayment::class,
            'transfer'=>P2pTransfer::class, 'portfolio_withdrawal'=>PortfolioWithdrawal::class, 'crypto_swap'=>Conversion::class,
            'gift_card_purchase'=>GiftCardOrder::class];
        $types['gift_card_sale'] = GiftCardSaleOrder::class;
        $rows = collect();
        foreach ($types as $type=>$model) {
            if ($request->filled('type') && $request->query('type') !== $type) continue;
            $query = $model::query()->latest();
            if ($type === 'crypto_swap') $query->where('mode', 'nowpayments');
            if ($request->filled('user_id')) {
                if ($type === 'transfer') $query->where(fn($q) => $q->where('sender_id', $request->integer('user_id'))->orWhere('recipient_id', $request->integer('user_id')));
                else $query->where('user_id', $request->integer('user_id'));
            }
            if ($request->filled('status')) $query->where('status', $request->query('status'));
            if ($request->filled('date')) $query->whereDate('created_at', $request->query('date'));
            if ($request->filled('search')) {
                $needle = '%'.$request->query('search').'%';
                $columns = match($type) {
                    'buy','sell'=>['transaction_ref','coin','wallet_address'], 'deposit'=>['transaction_ref','currency','gateway_reference'],
                    'withdrawal'=>['reference','currency','bank_account'], 'crypto_deposit'=>['transaction_hash','currency','payment_address','payment_data->provider_payment_id'],
                    'crypto_withdrawal'=>['transaction_hash','address','network'], 'bill_payment'=>['reference','provider_reference','category'],
                    'transfer'=>['reference','note'], 'portfolio_withdrawal'=>['reference'], 'crypto_swap'=>['from_coin','to_coin','provider_conversion_id'],
                    'gift_card_purchase','gift_card_sale'=>['reference','card_name','country'], default=>[]
                };
                $query->where(function ($q) use ($needle, $columns, $type) {
                    if ($type === 'transfer') $q->whereHas('sender', fn($u)=>$u->where('name','like',$needle)->orWhere('email','like',$needle))->orWhereHas('recipient', fn($u)=>$u->where('name','like',$needle)->orWhere('email','like',$needle));
                    else $q->whereHas('user', fn($u)=>$u->where('name','like',$needle)->orWhere('email','like',$needle));
                    foreach ($columns as $column) $q->orWhere($column,'like',$needle);
                });
            }
            foreach ($query->get() as $record) {
                $user = $type === 'transfer' ? $record->sender : $record->user;
                $amount = match($type) {
                    'buy' => $record->naira_amount, 'sell' => $record->naira_amount ?? $record->amount,
                    'crypto_deposit' => $record->received_amount ?: $record->expectedCryptoAmount(),
                    'crypto_withdrawal' => $record->amount,
                    'bill_payment' => $record->amount + $record->fee,
                    'transfer' => $record->amount,
                    'portfolio_withdrawal' => $record->naira_amount,
                    'crypto_swap' => $record->from_amount,
                    'gift_card_purchase' => $record->total_ngn,
                    'gift_card_sale' => $record->approved_ngn ?? $record->estimated_ngn,
                    default => $record->amount,
                };
                $editData = match ($type) {
                    'crypto_deposit' => [
                        'invoice_usd_amount' => $record->invoiceUsdAmount(),
                        'expected_crypto_amount' => $record->expectedCryptoAmount(),
                        'received_amount' => $record->received_amount,
                        'transaction_hash' => $record->transaction_hash,
                        'payment_address' => $record->payment_address,
                    ],
                    'crypto_withdrawal' => [
                        'amount' => $record->amount, 'fee' => $record->fee,
                        'address' => $record->address, 'network' => $record->network,
                        'transaction_hash' => $record->transaction_hash,
                    ],
                    'crypto_swap' => [
                        'to_coin' => $record->to_coin,
                        'from_amount' => $record->from_amount, 'to_amount' => $record->to_amount,
                        'fee_amount' => $record->fee_amount,
                    ],
                    default => [],
                };
                $rows->push((object)[
                    'type'=>$type, 'id'=>$record->id, 'user'=>$user,
                    'reference'=>$record->transaction_ref ?? $record->reference ?? $record->transaction_hash ?? $record->gateway_reference ?? $record->provider_conversion_id ?? data_get($record->payment_data,'provider_payment_id') ?? '—',
                    'currency'=>in_array($type,['gift_card_purchase','gift_card_sale'],true) ? 'NGN' : ($record->coin ?? $record->currency ?? ($type==='bill_payment' ? $record->category : ($type==='crypto_swap' ? $record->from_coin : 'NGN'))),
                    'amount'=>$amount, 'status'=>$record->status, 'created_at'=>$record->created_at,
                    'fee_amount'=>(float) ($record->fee_amount ?? 0),
                    'edit_data'=>$editData,
                ]);
            }
        }
        $rows = $rows->sortByDesc('created_at')->values();
        $perPage = min(max((int)$request->query('per_page', 25), 10), 100);
        $page = LengthAwarePaginator::resolveCurrentPage();
        $transactions = new LengthAwarePaginator($rows->forPage($page,$perPage)->values(), $rows->count(), $perPage, $page, ['path'=>$request->url(),'query'=>$request->query()]);
        return view('admin.transactions.index', compact('transactions'));
    }
}
