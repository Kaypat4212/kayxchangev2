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
use App\Models\AdminAuditLog;
use App\Services\BalanceSettlementService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminTransactionHistoryController extends Controller
{
    public function update(Request $request, string $type, int $id)
    {
        abort_unless(in_array($type, ['deposit', 'withdrawal', 'sell', 'buy'], true), 404);
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:100000000000'],
            'fee_amount' => ['nullable', 'numeric', 'min:0', 'max:100000000000'],
            'status' => ['required', 'in:pending,approved,successful,completed,rejected,failed,cancelled'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);
        $delta = DB::transaction(function () use ($request, $type, $id, $data) {
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
            return $balanceDelta;
        });
        return back()->with('success', 'Transaction updated directly. Balance adjustment: ₦' . number_format($delta, 2) . '.');
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
                    'withdrawal'=>['reference','currency','bank_account'], 'crypto_deposit'=>['transaction_hash','currency','payment_address'],
                    'crypto_withdrawal'=>['transaction_hash','address','network'], 'bill_payment'=>['reference','provider_reference','category'],
                    'transfer'=>['reference','note'], 'portfolio_withdrawal'=>['reference'], 'crypto_swap'=>['from_coin','to_coin','provider_conversion_id'],
                    'gift_card_purchase'=>['reference','card_name','country','recipient_email'], default=>[]
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
                    'crypto_deposit' => $record->received_amount ?? $record->expected_amount,
                    'crypto_withdrawal' => $record->amount,
                    'bill_payment' => $record->amount + $record->fee,
                    'transfer' => $record->amount,
                    'portfolio_withdrawal' => $record->naira_amount,
                    'crypto_swap' => $record->from_amount,
                    'gift_card_purchase' => $record->total_ngn,
                    default => $record->amount,
                };
                $rows->push((object)[
                    'type'=>$type, 'id'=>$record->id, 'user'=>$user,
                    'reference'=>$record->transaction_ref ?? $record->reference ?? $record->transaction_hash ?? $record->gateway_reference ?? $record->provider_conversion_id ?? '—',
                    'currency'=>$type==='gift_card_purchase' ? 'NGN' : ($record->coin ?? $record->currency ?? ($type==='bill_payment' ? $record->category : ($type==='crypto_swap' ? $record->from_coin : 'NGN'))),
                    'amount'=>$amount, 'status'=>$record->status, 'created_at'=>$record->created_at,
                    'fee_amount'=>(float) ($record->fee_amount ?? 0),
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
