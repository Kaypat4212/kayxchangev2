<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\BuyTrade;
use App\Models\SellTrade;
use App\Models\Withdrawal;
use App\Models\Kyc;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->is_admin) {
                abort(403, 'Unauthorized');
            }
            return $next($request);
        });
    }

    /**
     * Get real-time dashboard analytics
     */
    public function getDashboardData()
    {
        // Cache analytics for 5 minutes to improve performance
        return Cache::remember('admin_dashboard_analytics', 300, function () {
            return [
                'overview' => $this->getOverviewStats(),
                'trading' => $this->getTradingStats(),
                'users' => $this->getUserStats(),
                'revenue' => $this->getRevenueStats(),
                'system' => $this->getSystemHealth(),
                'recent_activities' => $this->getRecentActivities(),
                'charts' => $this->getChartData()
            ];
        });
    }

    /**
     * Get overview statistics
     */
    private function getOverviewStats()
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        return [
            'total_users' => User::count(),
            'new_users_today' => User::whereDate('created_at', $today)->count(),
            'new_users_growth' => $this->calculateGrowth(
                User::whereDate('created_at', $today)->count(),
                User::whereDate('created_at', $yesterday)->count()
            ),
            'active_trades' => BuyTrade::where('status', 'pending')->count() + SellTrade::where('status', 'pending')->count(),
            'pending_withdrawals' => Withdrawal::where('status', 'pending')->count(),
            'pending_kyc' => Kyc::where('status', 'pending')->count(),
            'total_volume_today' => $this->getTodayVolume(),
            'telegram_connected' => User::whereNotNull('telegram_chat_id')->where('telegram_verified', true)->count(),
        ];
    }

    /**
     * Get trading statistics
     */
    private function getTradingStats()
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        
        return [
            'buy_trades_today' => BuyTrade::whereDate('created_at', $today)->count(),
            'sell_trades_today' => SellTrade::whereDate('created_at', $today)->count(),
            'completed_trades_today' => BuyTrade::whereDate('created_at', $today)->where('status', 'completed')->count() +
                                      SellTrade::whereDate('created_at', $today)->where('status', 'completed')->count(),
            'popular_coins' => $this->getPopularCoins(),
            'average_trade_size' => $this->getAverageTradeSize(),
            'success_rate' => $this->getTradingSuccessRate(),
        ];
    }

    /**
     * Get user statistics
     */
    private function getUserStats()
    {
        return [
            'verified_users' => User::where('email_verified_at', '!=', null)->count(),
            'kyc_verified' => User::where('kyc_verified', true)->count(),
            'telegram_users' => User::where('telegram_verified', true)->count(),
            'active_users_7days' => User::where('updated_at', '>=', Carbon::now()->subDays(7))->count(),
            'user_growth_30days' => $this->getUserGrowth(30),
            'top_traders' => $this->getTopTraders(),
        ];
    }

    /**
     * Get revenue statistics
     */
    private function getRevenueStats()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        
        return [
            'revenue_today' => $this->calculateRevenue($today, $today->copy()->endOfDay()),
            'revenue_this_month' => $this->calculateRevenue($thisMonth, Carbon::now()),
            'withdrawal_fees' => $this->getWithdrawalFees(),
            'commission_earned' => $this->getCommissionEarned(),
        ];
    }

    /**
     * Get system health metrics
     */
    private function getSystemHealth()
    {
        $telegramService = new TelegramService();
        
        return [
            'database_status' => $this->checkDatabaseHealth(),
            'telegram_bot_status' => $this->checkTelegramBotHealth($telegramService),
            'storage_usage' => $this->getStorageUsage(),
            'cache_status' => $this->getCacheStatus(),
            'queue_status' => $this->getQueueStatus(),
            'response_time' => $this->getAverageResponseTime(),
        ];
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities()
    {
        $activities = collect();

        // Recent trades
        $recentTrades = BuyTrade::with('user')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($trade) {
                return [
                    'type' => 'buy_trade',
                    'message' => "New buy trade: {$trade->coin} worth $" . number_format($trade->usd_amount, 2),
                    'user' => $trade->user->name,
                    'time' => $trade->created_at,
                    'status' => $trade->status
                ];
            });

        // Recent users
        $recentUsers = User::latest()
            ->take(3)
            ->get()
            ->map(function ($user) {
                return [
                    'type' => 'new_user',
                    'message' => "New user registered: {$user->name}",
                    'user' => $user->name,
                    'time' => $user->created_at,
                    'status' => $user->email_verified_at ? 'verified' : 'pending'
                ];
            });

        return $activities->merge($recentTrades)->merge($recentUsers)
            ->sortByDesc('time')
            ->take(10)
            ->values();
    }

    /**
     * Get chart data for dashboard
     */
    private function getChartData()
    {
        return [
            'user_growth' => $this->getUserGrowthChart(),
            'trading_volume' => $this->getTradingVolumeChart(),
            'revenue_trend' => $this->getRevenueTrendChart(),
            'coin_distribution' => $this->getCoinDistributionChart(),
        ];
    }

    /**
     * Helper methods
     */
    private function calculateGrowth($current, $previous)
    {
        if ($previous == 0) return $current > 0 ? 100 : 0;
        return round((($current - $previous) / $previous) * 100, 2);
    }

    private function getTodayVolume()
    {
        $buyVolume = BuyTrade::whereDate('created_at', Carbon::today())->sum('usd_amount') ?? 0;
        $sellVolume = SellTrade::whereDate('created_at', Carbon::today())->sum('usd_amount') ?? 0;
        return $buyVolume + $sellVolume;
    }

    private function getPopularCoins()
    {
        $buyCoins = BuyTrade::select('coin', DB::raw('count(*) as count'))
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('coin')
            ->orderByDesc('count')
            ->take(5)
            ->get();

        $sellCoins = SellTrade::select('coin', DB::raw('count(*) as count'))
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('coin')
            ->orderByDesc('count')
            ->take(5)
            ->get();

        return $buyCoins->merge($sellCoins)
            ->groupBy('coin')
            ->map(function ($coins, $coin) {
                return [
                    'coin' => $coin,
                    'count' => $coins->sum('count')
                ];
            })
            ->sortByDesc('count')
            ->take(5)
            ->values();
    }

    private function getAverageTradeSize()
    {
        $buyAvg = BuyTrade::avg('usd_amount') ?? 0;
        $sellAvg = SellTrade::avg('usd_amount') ?? 0;
        return ($buyAvg + $sellAvg) / 2;
    }

    private function getTradingSuccessRate()
    {
        $totalTrades = BuyTrade::count() + SellTrade::count();
        $completedTrades = BuyTrade::where('status', 'completed')->count() + 
                          SellTrade::where('status', 'completed')->count();
        
        return $totalTrades > 0 ? round(($completedTrades / $totalTrades) * 100, 2) : 0;
    }

    private function getUserGrowth($days)
    {
        return User::where('created_at', '>=', Carbon::now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getTopTraders()
    {
        return User::withCount(['buyTrades', 'sellTrades'])
            ->having('buy_trades_count', '>', 0)
            ->orHaving('sell_trades_count', '>', 0)
            ->orderByDesc(DB::raw('buy_trades_count + sell_trades_count'))
            ->take(10)
            ->get();
    }

    private function calculateRevenue($startDate, $endDate)
    {
        // Calculate commission from trades (assuming 1% commission)
        $buyRevenue = BuyTrade::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->sum('usd_amount') * 0.01;

        $sellRevenue = SellTrade::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->sum('usd_amount') * 0.01;

        return $buyRevenue + $sellRevenue;
    }

    private function getWithdrawalFees()
    {
        return Withdrawal::where('status', 'completed')
            ->whereDate('created_at', Carbon::today())
            ->sum('fee') ?? 0;
    }

    private function getCommissionEarned()
    {
        $today = Carbon::today();
        return $this->calculateRevenue($today, $today->copy()->endOfDay());
    }

    private function checkDatabaseHealth()
    {
        try {
            DB::connection()->getPdo();
            $queryTime = microtime(true);
            DB::select('SELECT 1');
            $queryTime = (microtime(true) - $queryTime) * 1000;
            
            return [
                'status' => 'healthy',
                'response_time' => round($queryTime, 2) . 'ms'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    private function checkTelegramBotHealth($telegramService)
    {
        try {
            $botInfo = $telegramService->getBotInfo();
            return [
                'status' => $botInfo && $botInfo['ok'] ? 'healthy' : 'error',
                'bot_name' => $botInfo['result']['first_name'] ?? 'Unknown',
                'username' => $botInfo['result']['username'] ?? 'Unknown',
                'webhook_mode' => $telegramService->isProductionMode()
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    private function getStorageUsage()
    {
        $storagePath = storage_path();
        $totalSize = $this->getDirSize($storagePath);
        
        return [
            'used' => $this->formatBytes($totalSize),
            'logs_size' => $this->formatBytes($this->getDirSize($storagePath . '/logs')),
            'cache_size' => $this->formatBytes($this->getDirSize($storagePath . '/framework/cache'))
        ];
    }

    private function getCacheStatus()
    {
        try {
            Cache::put('health_check', 'ok', 60);
            $value = Cache::get('health_check');
            return [
                'status' => $value === 'ok' ? 'healthy' : 'error',
                'driver' => config('cache.default')
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    private function getQueueStatus()
    {
        // Basic queue status - can be enhanced based on your queue driver
        return [
            'status' => 'healthy',
            'driver' => config('queue.default'),
            'pending_jobs' => 0 // Implement based on your queue system
        ];
    }

    private function getAverageResponseTime()
    {
        // Implement response time tracking - this is a placeholder
        return '120ms';
    }

    private function getUserGrowthChart()
    {
        return User::where('created_at', '>=', Carbon::now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date');
    }

    private function getTradingVolumeChart()
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $buyVolume = BuyTrade::whereDate('created_at', $date)->sum('usd_amount') ?? 0;
            $sellVolume = SellTrade::whereDate('created_at', $date)->sum('usd_amount') ?? 0;
            $data[$date] = $buyVolume + $sellVolume;
        }
        return $data;
    }

    private function getRevenueTrendChart()
    {
        $data = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $data[$date->toDateString()] = $this->calculateRevenue($date->startOfDay(), $date->endOfDay());
        }
        return $data;
    }

    private function getCoinDistributionChart()
    {
        $coins = $this->getPopularCoins();
        return $coins->pluck('count', 'coin');
    }

    private function getDirSize($dir)
    {
        $size = 0;
        if (is_dir($dir)) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir)) as $file) {
                $size += $file->getSize();
            }
        }
        return $size;
    }

    private function formatBytes($size, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $base = log($size, 1024);
        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $units[floor($base)];
    }
}
