<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BuyTrade;
use App\Models\SellTrade;
use App\Models\Withdrawal;
use App\Models\Referral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function getStats(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Get total transactions count
            $totalBuyTrades = BuyTrade::where('user_id', $user->id)->count();
            $totalSellTrades = SellTrade::where('user_id', $user->id)->count();
            $totalWithdrawals = Withdrawal::where('user_id', $user->id)->count();
            $totalTransactions = $totalBuyTrades + $totalSellTrades + $totalWithdrawals;
            
            // Get active trades
            $activeBuyTrades = BuyTrade::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'processing'])
                ->count();
            $activeSellTrades = SellTrade::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'processing'])
                ->count();
            $activeTrades = $activeBuyTrades + $activeSellTrades;
            
            // Get referrals count
            $referralsCount = Referral::where('referrer_id', $user->id)->count();
            
            // Get portfolio value over time (last 30 days)
            $portfolioData = $this->getPortfolioData($user->id);
            
            // Get monthly transaction volume
            $monthlyVolume = $this->getMonthlyVolume($user->id);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'totalTransactions' => $totalTransactions,
                    'activeTrades' => $activeTrades,
                    'referrals' => $referralsCount,
                    'totalBalance' => $user->balance,
                    'portfolioData' => $portfolioData,
                    'monthlyVolume' => $monthlyVolume,
                    'kycVerified' => $user->kyc_verified,
                    'lastLogin' => $user->updated_at->diffForHumans(),
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching dashboard stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get recent transactions
     */
    public function getRecentTransactions(Request $request)
    {
        try {
            $user = Auth::user();
            $limit = $request->get('limit', 10);
            
            $transactions = collect();
            
            // Get buy trades
            $buyTrades = BuyTrade::where('user_id', $user->id)
                ->latest()
                ->take($limit)
                ->get()
                ->map(function ($trade) {
                    return [
                        'id' => $trade->id,
                        'type' => 'buy',
                        'coin' => $trade->coin,
                        'amount_ngn' => $trade->naira_amount,
                        'amount_usd' => $trade->usd_amount,
                        'status' => $trade->status,
                        'created_at' => $trade->created_at,
                        'updated_at' => $trade->updated_at,
                    ];
                });
            
            // Get sell trades
            $sellTrades = SellTrade::where('user_id', $user->id)
                ->latest()
                ->take($limit)
                ->get()
                ->map(function ($trade) {
                    return [
                        'id' => $trade->id,
                        'type' => 'sell',
                        'coin' => $trade->coin,
                        'amount_ngn' => $trade->amount,
                        'amount_usd' => $trade->usd_amount ?? 0,
                        'status' => $trade->status,
                        'created_at' => $trade->created_at,
                        'updated_at' => $trade->updated_at,
                    ];
                });
            
            // Get withdrawals
            $withdrawals = Withdrawal::where('user_id', $user->id)
                ->latest()
                ->take($limit)
                ->get()
                ->map(function ($withdrawal) {
                    return [
                        'id' => $withdrawal->id,
                        'type' => 'withdrawal',
                        'bank_account' => $withdrawal->bank_account_number ?? 'N/A',
                        'amount_ngn' => $withdrawal->amount,
                        'amount_usd' => null,
                        'status' => $withdrawal->status,
                        'created_at' => $withdrawal->created_at,
                        'updated_at' => $withdrawal->updated_at,
                    ];
                });
            
            // Merge and sort all transactions
            $allTransactions = $buyTrades
                ->concat($sellTrades)
                ->concat($withdrawals)
                ->sortByDesc('created_at')
                ->take($limit)
                ->values();
            
            return response()->json([
                'success' => true,
                'data' => $allTransactions
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching recent transactions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get portfolio performance data
     */
    public function getPortfolioData($userId, $days = 30)
    {
        try {
            $endDate = Carbon::now();
            $startDate = $endDate->copy()->subDays($days);
            
            $data = [];
            $currentDate = $startDate->copy();
            
            while ($currentDate <= $endDate) {
                // Calculate balance at this point in time
                $balance = $this->calculateBalanceAtDate($userId, $currentDate);
                
                $data[] = [
                    'date' => $currentDate->format('Y-m-d'),
                    'balance' => $balance,
                ];
                
                $currentDate->addDay();
            }
            
            return $data;
            
        } catch (\Exception $e) {
            // Return default data if calculation fails
            return [
                ['date' => Carbon::now()->subDays(30)->format('Y-m-d'), 'balance' => 0],
                ['date' => Carbon::now()->format('Y-m-d'), 'balance' => Auth::user()->balance],
            ];
        }
    }
    
    /**
     * Calculate user balance at a specific date
     */
    private function calculateBalanceAtDate($userId, $date)
    {
        // This is a simplified calculation
        // In a real application, you might want to track balance changes over time
        $user = \App\Models\User::find($userId);
        
        // For now, return current balance (you can implement historical tracking)
        return $user->balance ?? 0;
    }
    
    /**
     * Get monthly transaction volume
     */
    private function getMonthlyVolume($userId)
    {
        $currentMonth = Carbon::now()->startOfMonth();
        
        $buyVolume = BuyTrade::where('user_id', $userId)
            ->where('created_at', '>=', $currentMonth)
            ->where('status', 'completed')
            ->sum('naira_amount') ?? 0;
            
        $sellVolume = SellTrade::where('user_id', $userId)
            ->where('created_at', '>=', $currentMonth)
            ->where('status', 'completed')
            ->sum('amount') ?? 0;
            
        return $buyVolume + $sellVolume;
    }
    
    /**
     * Get trading performance analytics
     */
    public function getTradingAnalytics(Request $request)
    {
        try {
            $user = Auth::user();
            $period = $request->get('period', '30d');
            
            $days = match($period) {
                '7d' => 7,
                '30d' => 30,
                '90d' => 90,
                default => 30
            };
            
            $startDate = Carbon::now()->subDays($days);
            
            // Get buy/sell ratio
            $buyCount = BuyTrade::where('user_id', $user->id)
                ->where('created_at', '>=', $startDate)
                ->count();
                
            $sellCount = SellTrade::where('user_id', $user->id)
                ->where('created_at', '>=', $startDate)
                ->count();
            
            // Get average transaction amounts
            $avgBuyAmount = BuyTrade::where('user_id', $user->id)
                ->where('created_at', '>=', $startDate)
                ->where('status', 'completed')
                ->avg('naira_amount') ?? 0;
                
            $avgSellAmount = SellTrade::where('user_id', $user->id)
                ->where('created_at', '>=', $startDate)
                ->where('status', 'completed')
                ->avg('amount') ?? 0;
            
            // Get most traded coins
            $topCoins = DB::table(DB::raw('(
                SELECT coin, COUNT(*) as count FROM buy_trades WHERE user_id = ? AND created_at >= ?
                UNION ALL
                SELECT coin, COUNT(*) as count FROM sell_trades WHERE user_id = ? AND created_at >= ?
            ) as combined'))
            ->setBindings([$user->id, $startDate, $user->id, $startDate])
            ->select('coin', DB::raw('SUM(count) as total_count'))
            ->groupBy('coin')
            ->orderBy('total_count', 'desc')
            ->limit(5)
            ->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'period' => $period,
                    'buyCount' => $buyCount,
                    'sellCount' => $sellCount,
                    'avgBuyAmount' => round($avgBuyAmount, 2),
                    'avgSellAmount' => round($avgSellAmount, 2),
                    'topCoins' => $topCoins,
                    'totalVolume' => $this->getMonthlyVolume($user->id),
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching trading analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get notification data
     */
    public function getNotifications(Request $request)
    {
        try {
            $user = Auth::user();
            $notifications = [];
            
            // Check for pending KYC
            if (!$user->kyc_verified) {
                $notifications[] = [
                    'type' => 'warning',
                    'title' => 'KYC Verification Required',
                    'message' => 'Complete your KYC verification to access all features',
                    'action_url' => route('kyc.form'),
                    'action_text' => 'Verify Now',
                    'created_at' => Carbon::now()
                ];
            }
            
            // Check for pending trades
            $pendingTrades = BuyTrade::where('user_id', $user->id)
                ->where('status', 'pending')
                ->count() + 
                SellTrade::where('user_id', $user->id)
                ->where('status', 'pending')
                ->count();
                
            if ($pendingTrades > 0) {
                $notifications[] = [
                    'type' => 'info',
                    'title' => 'Pending Trades',
                    'message' => "You have {$pendingTrades} pending trade(s)",
                    'action_url' => route('transactions.history'),
                    'action_text' => 'View Trades',
                    'created_at' => Carbon::now()
                ];
            }
            
            // Check for successful completions in last 24 hours
            $recentCompletions = BuyTrade::where('user_id', $user->id)
                ->where('status', 'completed')
                ->where('updated_at', '>=', Carbon::now()->subDay())
                ->count() +
                SellTrade::where('user_id', $user->id)
                ->where('status', 'completed')
                ->where('updated_at', '>=', Carbon::now()->subDay())
                ->count();
                
            if ($recentCompletions > 0) {
                $notifications[] = [
                    'type' => 'success',
                    'title' => 'Trades Completed',
                    'message' => "{$recentCompletions} trade(s) completed successfully",
                    'action_url' => route('transactions.history'),
                    'action_text' => 'View Details',
                    'created_at' => Carbon::now()
                ];
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'notifications' => $notifications,
                    'unread_count' => count($notifications)
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}