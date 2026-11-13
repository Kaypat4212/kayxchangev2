<?php

namespace App\Http\Controllers;

use App\Models\BuyTrade;
use App\Models\Deposit;
use App\Models\SellTrade;
use App\Models\Withdrawal;
use App\Models\CryptoDeposit;
use App\Models\CryptoWithdrawal;
use App\Models\BillPayment;
use App\Models\P2pTransfer;
use App\Models\Conversion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserTransactionController extends Controller
{
    public function showTransactions(Request $request)
    {
        $user = Auth::user();
        $search = $request->query('search', '');
        $status = $request->query('status', '');
        $type = $request->query('type', '');
        $date = $request->query('date', '');
        $minAmount = $request->query('min_amount', '');
        $maxAmount = $request->query('max_amount', '');
        $perPage = $request->query('per_page', 10);

        // Base queries
        $buyQuery = BuyTrade::where('user_id', $user->id)
            ->select([
                'id',
                'coin',
                'usd_amount as amount',
                'naira_amount',
                DB::raw('usd_amount as usd_amount'),
                'status',
                DB::raw('COALESCE(payment_method, network) as payment_method'),
                'wallet_address',
                'created_at',
                DB::raw('"buy" as type'),
                'transaction_ref as reference',
            ]);

        $sellQuery = SellTrade::where('user_id', $user->id)
            ->select([
                'id',
                'coin',
                'naira_amount as amount',
                'naira_amount',
                'usd_amount',
                'status',
                'payment_method',
                'wallet_address',
                'created_at',
                DB::raw('"sell" as type'),
                'transaction_ref as reference',
            ]);

        $withdrawalQuery = Withdrawal::where('user_id', $user->id)
            ->select([
                'id',
                'currency as coin',
                'amount',
                'amount as naira_amount',
                DB::raw('NULL as usd_amount'),
                'status',
                'bank_account as payment_method',
                DB::raw('NULL as wallet_address'),
                'created_at',
                DB::raw('"withdrawal" as type'),
                'reference',
            ]);

        $depositQuery = Deposit::where('user_id', $user->id)->select([
            'id', DB::raw('currency as coin'), 'amount', DB::raw('amount as naira_amount'),
            DB::raw('NULL as usd_amount'), 'status', DB::raw('payment_method as payment_method'),
            DB::raw('NULL as wallet_address'), 'created_at', DB::raw('"deposit" as type'),
            DB::raw('transaction_ref as reference'),
        ]);
        $cryptoDepositQuery = CryptoDeposit::where('user_id', $user->id)->select([
            'id', 'currency as coin', DB::raw('COALESCE(received_amount, expected_amount) as amount'),
            DB::raw('NULL as naira_amount'), DB::raw('NULL as usd_amount'), 'status',
            DB::raw('"Crypto wallet" as payment_method'), 'payment_address as wallet_address',
            'created_at', DB::raw('"crypto_deposit" as type'), DB::raw('transaction_hash as reference'),
        ]);
        $cryptoWithdrawalQuery = CryptoWithdrawal::where('user_id', $user->id)->select([
            'id', DB::raw('"Crypto" as coin'), 'amount', DB::raw('NULL as naira_amount'),
            DB::raw('NULL as usd_amount'), 'status', 'network as payment_method', 'address as wallet_address',
            'created_at', DB::raw('"crypto_withdrawal" as type'), DB::raw('transaction_hash as reference'),
        ]);
        $billQuery = BillPayment::where('user_id', $user->id)->select([
            'id', DB::raw('category as coin'), DB::raw('(amount + fee) as amount'),
            DB::raw('(amount + fee) as naira_amount'), DB::raw('NULL as usd_amount'), 'status',
            DB::raw('provider as payment_method'), 'customer_identifier as wallet_address',
            'created_at', DB::raw('"bill_payment" as type'), DB::raw('reference as reference'),
        ]);
        $p2pQuery = P2pTransfer::where(function ($q) use ($user) {
            $q->where('sender_id', $user->id)->orWhere('recipient_id', $user->id);
        })->select([
            'id', DB::raw('"NGN" as coin'), DB::raw('CASE WHEN sender_id = '.(int) $user->id.' THEN amount ELSE recipient_amount END as amount'),
            DB::raw('CASE WHEN sender_id = '.(int) $user->id.' THEN amount ELSE recipient_amount END as naira_amount'),
            DB::raw('NULL as usd_amount'), 'status', DB::raw('"Wallet transfer" as payment_method'),
            DB::raw('NULL as wallet_address'), 'created_at', DB::raw('"transfer" as type'), DB::raw('reference as reference'),
        ]);
        $cryptoSwapQuery = Conversion::where('user_id', $user->id)->where('mode', 'nowpayments')->select([
            'id', 'from_coin as coin', 'from_amount as amount', DB::raw('NULL as naira_amount'),
            DB::raw('NULL as usd_amount'), 'status', DB::raw('to_coin as payment_method'),
            DB::raw('NULL as wallet_address'), 'created_at', DB::raw('"crypto_swap" as type'),
            DB::raw("COALESCE(provider_conversion_id, CONCAT('SWAP-', id)) as reference"),
        ]);

        // Apply filters
        if ($search) {
            $buyQuery->where(function ($q) use ($search) {
                $q->where('coin', 'like', "%{$search}%")
                  ->orWhere('transaction_ref', 'like', "%{$search}%")
                  ->orWhere('wallet_address', 'like', "%{$search}%");
            });
            $sellQuery->where(function ($q) use ($search) {
                $q->where('coin', 'like', "%{$search}%")
                  ->orWhere('transaction_ref', 'like', "%{$search}%")
                  ->orWhere('wallet_address', 'like', "%{$search}%");
            });
            $withdrawalQuery->where(function ($q) use ($search) {
                $q->where('currency', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%")
                  ->orWhere('bank_account', 'like', "%{$search}%");
            });
            $depositQuery->where(function ($q) use ($search) { $q->where('currency', 'like', "%{$search}%")->orWhere('transaction_ref', 'like', "%{$search}%"); });
            $cryptoDepositQuery->where(function ($q) use ($search) { $q->where('currency', 'like', "%{$search}%")->orWhere('transaction_hash', 'like', "%{$search}%"); });
            $cryptoWithdrawalQuery->where(function ($q) use ($search) { $q->where('network', 'like', "%{$search}%")->orWhere('transaction_hash', 'like', "%{$search}%")->orWhere('address', 'like', "%{$search}%"); });
            $billQuery->where(function ($q) use ($search) { $q->where('category', 'like', "%{$search}%")->orWhere('reference', 'like', "%{$search}%")->orWhere('provider_reference', 'like', "%{$search}%"); });
            $p2pQuery->where('reference', 'like', "%{$search}%");
            $cryptoSwapQuery->where(function ($q) use ($search) {
                $q->where('from_coin', 'like', "%{$search}%")
                    ->orWhere('to_coin', 'like', "%{$search}%")
                    ->orWhere('provider_conversion_id', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $buyQuery->where('status', $status);
            $sellQuery->where('status', $status);
            $withdrawalQuery->where('status', $status);
            foreach ([$depositQuery,$cryptoDepositQuery,$cryptoWithdrawalQuery,$billQuery,$p2pQuery,$cryptoSwapQuery] as $q) $q->where('status', $status);
        }

        if ($type) {
            $typeQueries = ['buy'=>$buyQuery,'sell'=>$sellQuery,'withdrawal'=>$withdrawalQuery,'deposit'=>$depositQuery,'crypto_deposit'=>$cryptoDepositQuery,'crypto_withdrawal'=>$cryptoWithdrawalQuery,'crypto_swap'=>$cryptoSwapQuery,'bill_payment'=>$billQuery,'transfer'=>$p2pQuery];
            if (isset($typeQueries[$type])) foreach ($typeQueries as $queryType=>$queryRef) if ($queryType !== $type) $queryRef->whereRaw('1 = 0');
        }

        if ($date) {
            $buyQuery->whereDate('created_at', $date);
            $sellQuery->whereDate('created_at', $date);
            $withdrawalQuery->whereDate('created_at', $date);
            foreach ([$depositQuery,$cryptoDepositQuery,$cryptoWithdrawalQuery,$billQuery,$p2pQuery,$cryptoSwapQuery] as $q) $q->whereDate('created_at', $date);
        }

        if ($minAmount) {
            $buyQuery->where('naira_amount', '>=', $minAmount);
            $sellQuery->where('naira_amount', '>=', $minAmount);
            $withdrawalQuery->where('amount', '>=', $minAmount);
            foreach ([$depositQuery,$cryptoDepositQuery,$cryptoWithdrawalQuery,$billQuery,$p2pQuery,$cryptoSwapQuery] as $q) $q->where('amount', '>=', $minAmount);
        }

        if ($maxAmount) {
            $buyQuery->where('naira_amount', '<=', $maxAmount);
            $sellQuery->where('naira_amount', '<=', $maxAmount);
            $withdrawalQuery->where('amount', '<=', $maxAmount);
            foreach ([$depositQuery,$cryptoDepositQuery,$cryptoWithdrawalQuery,$billQuery,$p2pQuery,$cryptoSwapQuery] as $q) $q->where('amount', '<=', $maxAmount);
        }

        // Handle export
        if ($request->has('export')) {
            $allTrades = $buyQuery->get()
                ->merge($sellQuery->get())
                ->merge($withdrawalQuery->get())
                ->merge($depositQuery->get())->merge($cryptoDepositQuery->get())->merge($cryptoWithdrawalQuery->get())->merge($billQuery->get())->merge($p2pQuery->get())->merge($cryptoSwapQuery->get())
                ->sortByDesc('created_at');

            $csvData = [];
            $csvData[] = ['Type', 'Coin', 'Amount (NGN)', 'Amount (USD)', 'Status', 'Method', 'Wallet', 'Reference', 'Date'];

            foreach ($allTrades as $trade) {
                $method = $trade->type === 'withdrawal' && $trade->payment_method
                    ? (json_decode($trade->payment_method, true)['bank_name'] ?? 'N/A') . ' (' . (json_decode($trade->payment_method, true)['account_number'] ?? 'N/A') . ')'
                    : ($trade->payment_method ?? 'N/A');

                $csvData[] = [
                    ucfirst($trade->type),
                    $trade->coin ?? 'N/A',
                    '₦' . number_format($trade->naira_amount ?? $trade->amount, 2),
                    $trade->usd_amount ? '$' . number_format($trade->usd_amount, 2) : 'N/A',
                    ucfirst($trade->status),
                    $method,
                    $trade->wallet_address ?? 'N/A',
                    $trade->reference ?? 'N/A',
                    $trade->created_at->format('Y-m-d H:i:s'),
                ];
            }

            $filename = 'transactions_' . now()->format('Ymd_His') . '.csv';
            $handle = fopen('php://output', 'w');
            ob_start();

            foreach ($csvData as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            return response()->make(ob_get_clean(), 200, $headers);
        }

        // Combine queries with union for pagination
        $allTradesQuery = $buyQuery->getQuery()
            ->union($sellQuery->getQuery())
            ->union($withdrawalQuery->getQuery())->union($depositQuery->getQuery())->union($cryptoDepositQuery->getQuery())
            ->union($cryptoWithdrawalQuery->getQuery())->union($billQuery->getQuery())->union($p2pQuery->getQuery())->union($cryptoSwapQuery->getQuery());

        // Paginate the unified query
        $allTrades = DB::table(DB::raw("({$allTradesQuery->toSql()}) as trades"))
            ->mergeBindings($allTradesQuery)
            ->select([
                'id',
                'coin',
                'amount',
                'naira_amount',
                'usd_amount',
                'status',
                'payment_method',
                'wallet_address',
                DB::raw('CAST(created_at AS DATETIME) as created_at'),
                'type',
                'reference',
            ])
            ->orderByDesc('created_at')
            ->paginate($perPage);

        // Cast created_at to Carbon manually
        $allTrades->getCollection()->transform(function ($trade) {
            $trade->created_at = Carbon::parse($trade->created_at);
            return $trade;
        });

        return view('transactions.history', compact('allTrades'));
    }

    public function show(Request $request, string $type, int $id): JsonResponse
    {
        $user = Auth::user();

        switch ($type) {
            case 'buy':
                $record = BuyTrade::where('id', $id)->where('user_id', $user->id)->firstOrFail();
                $data = [
                    'type'             => 'buy',
                    'id'               => $record->id,
                    'coin'             => $record->coin,
                    'network'          => $record->network,
                    'usd_amount'       => $record->usd_amount,
                    'naira_amount'     => $record->naira_amount,
                    'rate_used'        => $record->rate_used,
                    'wallet_address'   => $record->wallet_address,
                    'payment_method'   => $record->payment_method,
                    'payment_proof'    => $record->payment_proof ? asset('storage/' . $record->payment_proof) : null,
                    'status'           => $record->status,
                    'reference'        => $record->transaction_ref,
                    'proof_upload_url' => ($record->status === 'pending' && ! $record->payment_proof) ? route('buy.payment', $record->id) : null,
                    'created_at'       => $record->created_at->format('d M Y, H:i'),
                    'updated_at'       => $record->updated_at->format('d M Y, H:i'),
                    'created_at_unix'  => $record->created_at->timestamp,
                ];
                break;

            case 'sell':
                $record = SellTrade::where('id', $id)->where('user_id', $user->id)->firstOrFail();
                $rawProof  = $record->proof ?? $record->payment_proof;
                $proofUrl  = ($rawProof && $rawProof !== 'bot_initiated') ? asset('storage/' . $rawProof) : null;
                $data = [
                    'type'             => 'sell',
                    'id'               => $record->id,
                    'coin'             => $record->coin,
                    'network'          => $record->network,
                    'usd_amount'       => $record->usd_amount,
                    'naira_amount'     => $record->naira_amount,
                    'rate_used'        => $record->rate_used,
                    'wallet_address'   => $record->wallet_address,
                    'bank_name'        => $record->bank_name,
                    'account_name'     => $record->account_name,
                    'account_number'   => $record->account_number,
                    'payment_method'   => $record->payment_method,
                    'payment_proof'    => $proofUrl,
                    'status'           => $record->status,
                    'reference'        => $record->transaction_ref,
                    'proof_upload_url' => ($record->status === 'pending' && ! $proofUrl) ? route('sell.payment', $record->id) : null,
                    'created_at'       => $record->created_at->format('d M Y, H:i'),
                    'updated_at'       => $record->updated_at->format('d M Y, H:i'),
                    'created_at_unix'  => $record->created_at->timestamp,
                ];
                break;

            case 'withdrawal':
                $record = Withdrawal::where('id', $id)->where('user_id', $user->id)->firstOrFail();
                $bankAccount = is_array($record->bank_account)
                    ? $record->bank_account
                    : (json_decode($record->bank_account, true) ?? []);
                $data = [
                    'type'          => 'withdrawal',
                    'id'            => $record->id,
                    'coin'          => $record->currency,
                    'amount'        => $record->amount,
                    'naira_amount'  => $record->amount,
                    'bank_name'     => $bankAccount['bank_name']    ?? ($record->payment_method ?? null),
                    'account_name'  => $bankAccount['account_name'] ?? null,
                    'account_number'=> $bankAccount['account_number'] ?? null,
                    'status'        => $record->status,
                    'reference'     => $record->reference,
                    'processed_at'  => $record->processed_at ? $record->processed_at->format('d M Y, H:i') : null,
                    'created_at'    => $record->created_at->format('d M Y, H:i'),
                    'updated_at'    => $record->updated_at->format('d M Y, H:i'),
                    'created_at_unix' => $record->created_at->timestamp,
                ];
                break;

            case 'deposit':
                $record = Deposit::where('id', $id)->where('user_id', $user->id)->firstOrFail();
                $data = [
                    'type'           => 'deposit',
                    'id'             => $record->id,
                    'coin'           => $record->currency ?? 'NGN',
                    'amount'         => $record->amount,
                    'naira_amount'   => $record->amount,
                    'payment_method' => ucfirst($record->payment_method ?? 'Bank Transfer'),
                    'status'         => $record->status,
                    'reference'      => $record->transaction_ref,
                    'processed_at'   => $record->processed_at ? $record->processed_at->format('d M Y, H:i') : null,
                    'created_at'     => $record->created_at->format('d M Y, H:i'),
                    'updated_at'     => $record->updated_at->format('d M Y, H:i'),
                    'created_at_unix' => $record->created_at->timestamp,
                ];
                break;

            case 'crypto_deposit':
                $record = CryptoDeposit::where('id', $id)->where('user_id', $user->id)->firstOrFail();
                $data = ['type'=>'crypto_deposit','id'=>$record->id,'coin'=>$record->currency,'amount'=>$record->received_amount ?? $record->expected_amount,'status'=>$record->status,'reference'=>$record->transaction_hash,'payment_address'=>$record->payment_address,'created_at'=>$record->created_at->format('d M Y, H:i'),'updated_at'=>$record->updated_at->format('d M Y, H:i'),'created_at_unix'=>$record->created_at->timestamp];
                break;
            case 'crypto_withdrawal':
                $record = CryptoWithdrawal::where('id', $id)->where('user_id', $user->id)->firstOrFail();
                $data = ['type'=>'crypto_withdrawal','id'=>$record->id,'coin'=>$record->wallet?->currency ?? 'Crypto','amount'=>$record->amount,'fee'=>$record->fee,'network'=>$record->network,'wallet_address'=>$record->address,'status'=>$record->status,'reference'=>$record->transaction_hash,'processed_at'=>$record->processed_at?->format('d M Y, H:i'),'created_at'=>$record->created_at->format('d M Y, H:i'),'updated_at'=>$record->updated_at->format('d M Y, H:i'),'created_at_unix'=>$record->created_at->timestamp];
                break;
            case 'crypto_swap':
                $record = Conversion::where('id', $id)->where('user_id', $user->id)->where('mode', 'nowpayments')->firstOrFail();
                $data = [
                    'type' => 'crypto_swap', 'id' => $record->id,
                    'coin' => $record->from_coin, 'from_coin' => $record->from_coin,
                    'to_coin' => $record->to_coin, 'amount' => $record->from_amount,
                    'from_amount' => $record->from_amount, 'to_amount' => $record->to_amount,
                    'fee' => $record->fee_amount, 'status' => $record->status,
                    'reference' => $record->provider_conversion_id ?: 'SWAP-'.$record->id,
                    'provider_status' => $record->provider_status,
                    'created_at' => $record->created_at->format('d M Y, H:i'),
                    'updated_at' => $record->updated_at->format('d M Y, H:i'),
                    'created_at_unix' => $record->created_at->timestamp,
                ];
                break;
            case 'bill_payment':
                $record = BillPayment::where('id', $id)->where('user_id', $user->id)->firstOrFail();
                $data = ['type'=>'bill_payment','id'=>$record->id,'coin'=>$record->category,'amount'=>$record->amount,'fee'=>$record->fee,'payment_method'=>$record->provider,'status'=>$record->status,'reference'=>$record->reference,'customer_name'=>$record->customer_name,'created_at'=>$record->created_at->format('d M Y, H:i'),'updated_at'=>$record->updated_at->format('d M Y, H:i'),'created_at_unix'=>$record->created_at->timestamp];
                break;
            case 'transfer':
                $record = P2pTransfer::where('id', $id)->where(fn($q)=>$q->where('sender_id',$user->id)->orWhere('recipient_id',$user->id))->firstOrFail();
                $data = ['type'=>'transfer','id'=>$record->id,'coin'=>'NGN','amount'=>$record->sender_id===$user->id?$record->amount:$record->recipient_amount,'status'=>$record->status,'reference'=>$record->reference,'note'=>$record->note,'created_at'=>$record->created_at->format('d M Y, H:i'),'updated_at'=>$record->updated_at->format('d M Y, H:i'),'created_at_unix'=>$record->created_at->timestamp];
                break;

            default:
                return response()->json(['error' => 'Invalid transaction type'], 400);
        }

        return response()->json($data);
    }
}
