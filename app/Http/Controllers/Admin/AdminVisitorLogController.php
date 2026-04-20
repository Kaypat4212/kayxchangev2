<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VisitorLog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminVisitorLogController extends Controller
{
    public function index(Request $request)
    {
        $query = VisitorLog::query();

        if ($request->filled('ip'))      $query->where('ip', 'like', '%' . $request->ip_filter . '%');
        if ($request->filled('ip_filter')) $query->where('ip', 'like', '%' . $request->ip_filter . '%');
        if ($request->filled('country')) $query->where('country', 'like', '%' . $request->country . '%');
        if ($request->filled('bot'))     $query->where('is_bot', $request->bot === 'yes');
        if ($request->filled('route'))   $query->where('route_name', $request->route_filter);

        $logs = $query->latest()->paginate(50)->withQueryString();

        $stats = [
            'total_today'   => VisitorLog::whereDate('created_at', today())->count(),
            'unique_ips'    => VisitorLog::whereDate('created_at', today())->distinct('ip')->count('ip'),
            'bots_today'    => VisitorLog::whereDate('created_at', today())->where('is_bot', true)->count(),
            'top_countries' => VisitorLog::whereDate('created_at', today())
                ->whereNotNull('country')
                ->selectRaw('country, COUNT(*) as cnt')
                ->groupBy('country')
                ->orderByDesc('cnt')
                ->limit(5)
                ->pluck('cnt', 'country'),
        ];

        return view('admin.visitor-logs.index', compact('logs', 'stats'));
    }

    public function export()
    {
        $filename = 'visitor-logs-' . now()->format('Y-m-d') . '.csv';

        return new StreamedResponse(function () {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Time', 'IP', 'Country', 'City', 'ISP', 'Browser', 'Platform', 'Mobile', 'Bot', 'Method', 'URL', 'Route', 'Referrer', 'User ID']);

            VisitorLog::latest()->chunk(500, function ($rows) use ($handle) {
                foreach ($rows as $r) {
                    fputcsv($handle, [
                        $r->created_at->format('Y-m-d H:i:s'),
                        $r->ip,
                        $r->country ?? '',
                        $r->city ?? '',
                        $r->isp ?? '',
                        $r->browser ?? '',
                        $r->platform ?? '',
                        $r->is_mobile ? 'Yes' : 'No',
                        $r->is_bot ? 'Yes' : 'No',
                        $r->method,
                        $r->url,
                        $r->route_name ?? '',
                        $r->referer ?? '',
                        $r->user_id ?? '',
                    ]);
                }
            });

            fclose($handle);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-cache, no-store',
        ]);
    }

    public function clear(Request $request)
    {
        $days = max(1, (int) $request->input('days', 30));
        $deleted = VisitorLog::where('created_at', '<', now()->subDays($days))->delete();
        return back()->with('success', "Cleared {$deleted} visitor log entries older than {$days} days.");
    }
}
