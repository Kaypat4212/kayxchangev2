<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BuyTrade;
use App\Models\SellTrade;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            abort_unless(auth()->user()?->is_admin, 403, 'Unauthorized');
            return $next($request);
        });
    }

    public function analyticsPage()
    {
        return view('admin.analytics');
    }

    public function getDashboardData(Request $request)
    {
        $days = (int) $request->query('days', 7);
        if (!in_array($days, [7, 14, 30, 90], true)) {
            $days = 7;
        }

        $cacheKey = 'admin_analytics_v2_' . $days;

        try {
            $data = Cache::remember($cacheKey, 60, fn () => $this->buildAnalytics($days));
            return response()->json($data)->header('Cache-Control', 'private, no-store');
        } catch (\Throwable $exception) {
            report($exception);
            return response()->json([
                'message' => 'Analytics could not be loaded. Please refresh and try again.',
            ], 500);
        }
    }

    private function buildAnalytics(int $days): array
    {
        $start = Carbon::today()->subDays($days - 1);
        $end = Carbon::tomorrow();

        $buyBase = BuyTrade::query()->whereBetween('created_at', [$start, $end]);
        $sellBase = SellTrade::query()->whereBetween('created_at', [$start, $end]);

        $buyTotal = (clone $buyBase)->count();
        $sellTotal = (clone $sellBase)->count();
        $buyCompleted = (clone $buyBase)->whereIn(DB::raw('LOWER(status)'), ['completed', 'approved', 'success', 'successful'])->count();
        $sellCompleted = (clone $sellBase)->whereIn(DB::raw('LOWER(status)'), ['completed', 'approved', 'success', 'successful'])->count();
        $buyPending = (clone $buyBase)->whereIn(DB::raw('LOWER(status)'), ['pending', 'processing', 'awaiting_admin_review'])->count();
        $sellPending = (clone $sellBase)->whereIn(DB::raw('LOWER(status)'), ['pending', 'processing', 'awaiting_admin_review'])->count();

        $buyByDay = (clone $buyBase)->selectRaw('DATE(created_at) as day, COALESCE(SUM(naira_amount), 0) as volume')
            ->groupBy('day')->pluck('volume', 'day');
        $sellByDay = (clone $sellBase)->selectRaw('DATE(created_at) as day, COALESCE(SUM(naira_amount), 0) as volume')
            ->groupBy('day')->pluck('volume', 'day');

        $withdrawalFees = Withdrawal::query()
            ->whereBetween('created_at', [$start, $end])
            ->whereIn(DB::raw('LOWER(status)'), ['completed', 'approved', 'success', 'successful'])
            ->selectRaw('DATE(created_at) as day, COALESCE(SUM(fee_amount), 0) as fees')
            ->groupBy('day')->pluck('fees', 'day');

        $coinCounts = (clone $buyBase)->selectRaw('UPPER(COALESCE(coin, \'UNKNOWN\')) as coin, COUNT(*) as total')
            ->groupBy('coin')->pluck('total', 'coin')->toArray();
        foreach ((clone $sellBase)->selectRaw('UPPER(COALESCE(coin, \'UNKNOWN\')) as coin, COUNT(*) as total')
            ->groupBy('coin')->pluck('total', 'coin') as $coin => $count) {
            $coinCounts[$coin] = ($coinCounts[$coin] ?? 0) + $count;
        }
        arsort($coinCounts);
        $coinCounts = array_slice($coinCounts, 0, 8, true);

        $labels = [];
        $buyVolumes = [];
        $sellVolumes = [];
        $feeValues = [];
        $coinLabels = [];
        $coinValues = [];
        foreach ($coinCounts as $coin => $count) {
            $coinLabels[] = $coin;
            $coinValues[] = (int) $count;
        }

        for ($date = $start->copy(); $date->lt($end); $date->addDay()) {
            $key = $date->toDateString();
            $labels[] = $date->format('M j');
            $buyVolumes[] = (float) ($buyByDay[$key] ?? 0);
            $sellVolumes[] = (float) ($sellByDay[$key] ?? 0);
            $feeValues[] = (float) ($withdrawalFees[$key] ?? 0);
        }

        return [
            'period_days' => $days,
            'trading' => [
                'total_buy' => $buyTotal,
                'total_sell' => $sellTotal,
                'completed_buy' => $buyCompleted,
                'completed_sell' => $sellCompleted,
                'pending_buy' => $buyPending,
                'pending_sell' => $sellPending,
            ],
            'charts' => [
                'trading_volume' => [
                    'labels' => $labels,
                    'datasets' => [
                        ['label' => 'Buy volume', 'data' => $buyVolumes],
                        ['label' => 'Sell volume', 'data' => $sellVolumes],
                    ],
                ],
                'coin_distribution' => [
                    'labels' => $coinLabels,
                    'datasets' => [['label' => 'Trade count', 'data' => $coinValues]],
                ],
                'revenue_trend' => [
                    'labels' => $labels,
                    'datasets' => [['label' => 'Withdrawal fees', 'data' => $feeValues]],
                ],
            ],
        ];
    }
}
