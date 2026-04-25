<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BuyTrade;
use App\Models\SellTrade;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ExportController extends Controller
{
    public function trades(Request $request)
    {
        $request->validate([
            'type'  => 'nullable|in:buy,sell,all',
            'from'  => 'nullable|date',
            'to'    => 'nullable|date|after_or_equal:from',
            'status'=> 'nullable|string|max:50',
        ]);

        $type  = $request->get('type', 'all');
        $from  = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $to    = $request->filled('to')   ? Carbon::parse($request->to)->endOfDay()     : Carbon::now()->endOfDay();
        $status = $request->get('status', '');

        $rows = [];
        $headers = ['ID', 'Type', 'User ID', 'Name', 'Coin', 'USD Amount', 'Naira Amount', 'Rate Used', 'Status', 'Payment Method', 'Bank Name', 'Account No.', 'Account Name', 'Created At'];

        if (in_array($type, ['sell', 'all'])) {
            $q = SellTrade::whereBetween('created_at', [$from, $to]);
            if ($status) $q->where('status', $status);
            foreach ($q->get() as $t) {
                $rows[] = [
                    $t->id, 'sell', $t->user_id, $t->name ?? '', $t->coin ?? '',
                    $t->usd_amount ?? '', $t->naira_amount ?? '', $t->rate_used ?? '',
                    $t->status, $t->payment_method ?? '', $t->bank_name ?? '',
                    $t->account_number ?? '', $t->account_name ?? '',
                    $t->created_at?->format('Y-m-d H:i:s') ?? '',
                ];
            }
        }

        if (in_array($type, ['buy', 'all'])) {
            $q = BuyTrade::whereBetween('created_at', [$from, $to]);
            if ($status) $q->where('status', $status);
            foreach ($q->get() as $t) {
                $rows[] = [
                    $t->id, 'buy', $t->user_id, $t->name ?? '', $t->coin ?? '',
                    $t->usd_amount ?? '', $t->naira_amount ?? '', $t->rate_used ?? '',
                    $t->status, $t->payment_method ?? '', '', '', $t->wallet_address ?? '',
                    $t->created_at?->format('Y-m-d H:i:s') ?? '',
                ];
            }
        }

        $filename = 'trades-export-' . $from->format('Ymd') . '-to-' . $to->format('Ymd') . '.csv';

        $callback = function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ]);
    }
}
