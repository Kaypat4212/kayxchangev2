<?php

namespace App\Http\Controllers;

use App\Models\Withdrawal;
use App\Models\Kyc;
use App\Models\BuyTrade;
use App\Models\SellTrade;
use Illuminate\Http\Request;
namespace App\Http\Controllers\Api;

class AdminApiController extends Controller
{
    public function getPendingCounts()
    {
        try {
            $pendingWithdrawals = Withdrawal::where('status', 'pending')->count();
            $pendingKyc = Kyc::where('status', 'pending')->count();
            $pendingBuyTrades = BuyTrade::where('status', 'pending')->count();
            $pendingSellTrades = SellTrade::where('status', 'pending')->count();
            $pendingTrades = $pendingBuyTrades + $pendingSellTrades;

            return response()->json([
                'pending_withdrawals' => $pendingWithdrawals,
                'pending_kyc' => $pendingKyc,
                'pending_trades' => $pendingTrades,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch counts'], 500);
        }
    }
}