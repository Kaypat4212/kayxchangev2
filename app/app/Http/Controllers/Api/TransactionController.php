<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BuyTrade;
use App\Models\SellTrade;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
   public function index(Request $request)
{
    $user = Auth::user();
    $search = $request->query('search', '');
    $type = $request->query('type', '');
    $status = $request->query('status', '');
    $date = $request->query('date', '');
    $limit = $request->query('limit', 10);

    $userId = $user->is_admin ? null : $user->id;

    // Define "vital" transaction criteria
    $vitalNairaThreshold = 100000; // Minimum Naira amount for vital transactions
    $vitalDateThreshold = now()->subDays(7); // Last 7 days
    $vitalStatuses = ['pending', 'completed']; // Only pending or completed

    $buyQuery = BuyTrade::query()
        ->when($userId, function ($query, $userId) {
            return $query->where('user_id', $userId);
        })
        ->where('naira_amount', '>=', $vitalNairaThreshold)
        ->where('created_at', '>=', $vitalDateThreshold)
        ->whereIn('status', $vitalStatuses)
        ->select([
            'id',
            \DB::raw('"buy" as type'),
            'coin',
            'usd_amount',
            'naira_amount',
            'status',
            'network',
            'wallet_address',
            'transaction_ref',
            'created_at',
            \DB::raw('NULL as bank_name'),
            \DB::raw('NULL as account_number'),
            \DB::raw('NULL as account_name')
        ]);

    $sellQuery = SellTrade::query()
        ->when($userId, function ($query, $userId) {
            return $query->where('user_id', $userId);
        })
        ->where('amount', '>=', $vitalNairaThreshold)
        ->where('created_at', '>=', $vitalDateThreshold)
        ->whereIn('status', $vitalStatuses)
        ->select([
            'id',
            \DB::raw('"sell" as type'),
            'coin',
            \DB::raw('NULL as usd_amount'),
            'amount as naira_amount',
            'status',
            \DB::raw('NULL as network'),
            'wallet_address',
            'transaction_ref',
            'created_at',
            \DB::raw('NULL as bank_name'),
            \DB::raw('NULL as account_number'),
            \DB::raw('NULL as account_name')
        ]);

    $withdrawalQuery = Withdrawal::query()
        ->when($userId, function ($query, $userId) {
            return $query->where('user_id', $userId);
        })
        ->where('amount', '>=', $vitalNairaThreshold)
        ->where('created_at', '>=', $vitalDateThreshold)
        ->whereIn('status', $vitalStatuses)
        ->select([
            'id',
            \DB::raw('"withdrawal" as type'),
            \DB::raw('NULL as coin'),
            \DB::raw('NULL as usd_amount'),
            'amount as naira_amount',
            'status',
            \DB::raw('NULL as network'),
            \DB::raw('NULL as wallet_address'),
            \DB::raw('NULL as transaction_ref'),
            'created_at',
            'bank_name',
            'account_number',
            'account_name'
        ]);

    // Apply filters (same as showTransactions)
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
            $q->where('bank_name', 'like', "%{$search}%")
              ->orWhere('account_number', 'like', "%{$search}%")
              ->orWhere('account_name', 'like', "%{$search}%");
        });
    }

    if ($type) {
        if ($type === 'buy') {
            $sellQuery = SellTrade::whereRaw('1 = 0');
            $withdrawalQuery = Withdrawal::whereRaw('1 = 0');
        } elseif ($type === 'sell') {
            $buyQuery = BuyTrade::whereRaw('1 = 0');
            $withdrawalQuery = Withdrawal::whereRaw('1 = 0');
        } elseif ($type === 'withdrawal') {
            $buyQuery = BuyTrade::whereRaw('1 = 0');
            $sellQuery = SellTrade::whereRaw('1 = 0');
        }
    }

    if ($status) {
        $buyQuery->where('status', $status);
        $sellQuery->where('status', $status);
        $withdrawalQuery->where('status', $status);
    }

    if ($date) {
        $buyQuery->whereDate('created_at', $date);
        $sellQuery->whereDate('created_at', $date);
        $withdrawalQuery->whereDate('created_at', $date);
    }

    $transactions = $buyQuery
        ->union($sellQuery)
        ->union($withdrawalQuery)
        ->orderBy('created_at', 'desc')
        ->paginate($limit);

    return response()->json($transactions);
}

    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:completed,pending,canceled']);
        $status = $request->status;

        $buyTrade = BuyTrade::find($id);
        $sellTrade = SellTrade::find($id);
        $withdrawal = Withdrawal::find($id);

        if ($buyTrade) {
            $buyTrade->update(['status' => $status]);
            Log::info('BuyTrade status updated', ['id' => $id, 'status' => $status]);
            return response()->json(['message' => 'Status updated']);
        } elseif ($sellTrade) {
            $sellTrade->update(['status' => $status]);
            Log::info('SellTrade status updated', ['id' => $id, 'status' => $status]);
            return response()->json(['message' => 'Status updated']);
        } elseif ($withdrawal) {
            $withdrawal->update(['status' => $status]);
            Log::info('Withdrawal status updated', ['id' => $id, 'status' => $status]);
            return response()->json(['message' => 'Status updated']);
        }

        Log::warning('Transaction not found', ['id' => $id]);
        return response()->json(['error' => 'Transaction not found'], 404);
    }

    public function destroy($id)
    {
        $buyTrade = BuyTrade::find($id);
        $sellTrade = SellTrade::find($id);
        $withdrawal = Withdrawal::find($id);

        if ($buyTrade) {
            $buyTrade->delete();
            Log::info('BuyTrade deleted', ['id' => $id]);
            return response()->json(['message' => 'Transaction deleted']);
        } elseif ($sellTrade) {
            $sellTrade->delete();
            Log::info('SellTrade deleted', ['id' => $id]);
            return response()->json(['message' => 'Transaction deleted']);
        } elseif ($withdrawal) {
            $withdrawal->delete();
            Log::info('Withdrawal deleted', ['id' => $id]);
            return response()->json(['message' => 'Transaction deleted']);
        }

        Log::warning('Transaction not found', ['id' => $id]);
        return response()->json(['error' => 'Transaction not found'], 404);
    }
}