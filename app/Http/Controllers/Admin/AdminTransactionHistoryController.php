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
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminTransactionHistoryController extends Controller
{
    public function index(Request $request)
    {
        $types = ['buy'=>BuyTrade::class, 'sell'=>SellTrade::class, 'deposit'=>Deposit::class,
            'withdrawal'=>Withdrawal::class, 'crypto_deposit'=>CryptoDeposit::class,
            'crypto_withdrawal'=>CryptoWithdrawal::class, 'bill_payment'=>BillPayment::class,
            'transfer'=>P2pTransfer::class];
        $rows = collect();
        foreach ($types as $type=>$model) {
            if ($request->filled('type') && $request->query('type') !== $type) continue;
            $query = $model::query()->latest();
            if ($request->filled('status')) $query->where('status', $request->query('status'));
            if ($request->filled('date')) $query->whereDate('created_at', $request->query('date'));
            if ($request->filled('search')) {
                $needle = '%'.$request->query('search').'%';
                $columns = match($type) {
                    'buy','sell'=>['transaction_ref','coin','wallet_address'], 'deposit'=>['transaction_ref','currency','gateway_reference'],
                    'withdrawal'=>['reference','currency','bank_account'], 'crypto_deposit'=>['transaction_hash','currency','payment_address'],
                    'crypto_withdrawal'=>['transaction_hash','address','network'], 'bill_payment'=>['reference','provider_reference','category'],
                    'transfer'=>['reference','note'], default=>[]
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
                    default => $record->amount,
                };
                $rows->push((object)[
                    'type'=>$type, 'id'=>$record->id, 'user'=>$user,
                    'reference'=>$record->transaction_ref ?? $record->reference ?? $record->transaction_hash ?? $record->gateway_reference ?? '—',
                    'currency'=>$record->coin ?? $record->currency ?? ($type==='bill_payment' ? $record->category : 'NGN'),
                    'amount'=>$amount, 'status'=>$record->status, 'created_at'=>$record->created_at,
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
