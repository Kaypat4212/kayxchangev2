<?php

namespace App\Http\Controllers;

use App\Models\BuyTrade;
use App\Models\SellTrade;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
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
                'network as payment_method',
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
                'created_at',
                DB::raw('"withdrawal" as type'),
                'reference',
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
        }

        if ($status) {
            $buyQuery->where('status', $status);
            $sellQuery->where('status', $status);
            $withdrawalQuery->where('status', $status);
        }

        if ($type) {
            if ($type === 'buy') {
                $sellQuery->whereRaw('1 = 0');
                $withdrawalQuery->whereRaw('1 = 0');
            } elseif ($type === 'sell') {
                $buyQuery->whereRaw('1 = 0');
                $withdrawalQuery->whereRaw('1 = 0');
            } elseif ($type === 'withdrawal') {
                $buyQuery->whereRaw('1 = 0');
                $sellQuery->whereRaw('1 = 0');
            }
        }

        if ($date) {
            $buyQuery->whereDate('created_at', $date);
            $sellQuery->whereDate('created_at', $date);
            $withdrawalQuery->whereDate('created_at', $date);
        }

        if ($minAmount) {
            $buyQuery->where('naira_amount', '>=', $minAmount);
            $sellQuery->where('naira_amount', '>=', $minAmount);
            $withdrawalQuery->where('amount', '>=', $minAmount);
        }

        if ($maxAmount) {
            $buyQuery->where('naira_amount', '<=', $maxAmount);
            $sellQuery->where('naira_amount', '<=', $maxAmount);
            $withdrawalQuery->where('amount', '<=', $maxAmount);
        }

        // Handle export
        if ($request->has('export')) {
            $allTrades = $buyQuery->get()
                ->merge($sellQuery->get())
                ->merge($withdrawalQuery->get())
                ->sortByDesc('created_at');

            $csvData = [];
            $csvData[] = ['Type', 'Coin', 'Amount (NGN)', 'Amount (USD)', 'Status', 'Method', 'Reference', 'Date'];

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
            ->union($withdrawalQuery->getQuery());

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
}