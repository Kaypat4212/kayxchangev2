<?php

namespace App\Console\Commands;

use App\Models\BuyTrade;
use App\Models\SellTrade;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;

class SendDailyTradeSummary extends Command
{
    protected $signature = 'digest:daily';
    protected $description = 'Send daily trade summary email digest to admin';

    public function handle(): void
    {
        $yesterday = Carbon::yesterday();
        $todayStart = $yesterday->copy()->startOfDay();
        $todayEnd   = $yesterday->copy()->endOfDay();

        $sellTrades = SellTrade::whereBetween('created_at', [$todayStart, $todayEnd])->get();
        $buyTrades  = BuyTrade::whereBetween('created_at', [$todayStart, $todayEnd])->get();
        $newUsers   = User::whereBetween('created_at', [$todayStart, $todayEnd])->count();

        $sellCompleted  = $sellTrades->where('status', 'completed');
        $buyApproved    = $buyTrades->whereIn('status', ['completed', 'approved']);

        $data = [
            'date'              => $yesterday->format('D, d M Y'),
            'total_sell'        => $sellTrades->count(),
            'completed_sell'    => $sellCompleted->count(),
            'pending_sell'      => $sellTrades->where('status', 'pending')->count(),
            'rejected_sell'     => $sellTrades->whereIn('status', ['rejected', 'cancelled'])->count(),
            'total_buy'         => $buyTrades->count(),
            'approved_buy'      => $buyApproved->count(),
            'pending_buy'       => $buyTrades->where('status', 'pending')->count(),
            'sell_volume_ngn'   => $sellCompleted->sum('naira_amount'),
            'buy_volume_ngn'    => $buyApproved->sum('naira_amount'),
            'sell_volume_usd'   => $sellCompleted->sum('usd_amount'),
            'buy_volume_usd'    => $buyApproved->sum('usd_amount'),
            'new_users'         => $newUsers,
            'total_volume_ngn'  => $sellCompleted->sum('naira_amount') + $buyApproved->sum('naira_amount'),
        ];

        $adminEmail = config('mail.admin_email', env('ADMIN_EMAIL', config('mail.from.address')));

        Mail::send('emails.daily-summary', $data, function ($m) use ($adminEmail, $data) {
            $m->to($adminEmail)
              ->subject('📊 Daily Trade Summary — ' . $data['date']);
        });

        $this->info('Daily digest sent to ' . $adminEmail);
    }
}
