<?php

namespace App\Console\Commands;

use App\Models\BuyTrade;
use App\Models\SellTrade;
use App\Services\AdminTradeAlertService;
use Illuminate\Console\Command;

class EscalatePendingTrades extends Command
{
    protected $signature = 'trades:escalate-pending';
    protected $description = 'Escalate pending buy/sell trades older than configured threshold';

    public function handle(AdminTradeAlertService $alerts): int
    {
        if (!config('trade_alerts.enabled', true)) {
            $this->info('Trade alerts disabled.');
            return self::SUCCESS;
        }

        $thresholdMinutes = max((int) config('trade_alerts.escalate_after_minutes', 30), 1);
        $cutoff = now()->subMinutes($thresholdMinutes);

        $buyTrades = BuyTrade::with('user')
            ->where('status', 'pending')
            ->where('created_at', '<=', $cutoff)
            ->get();

        $sellTrades = SellTrade::with('user')
            ->where('status', 'pending')
            ->where('created_at', '<=', $cutoff)
            ->get();

        $count = 0;

        foreach ($buyTrades as $trade) {
            $cacheKey = 'escalate:buy:' . $trade->id . ':' . $trade->status;
            if (! $alerts->shouldEscalate($cacheKey)) {
                continue;
            }

            $count++;
            $alerts->sendEscalationAlert('buy', [
                'reference' => $trade->transaction_ref ?? ('BUY-' . $trade->id),
                'user_name' => $trade->user->name ?? $trade->name ?? 'N/A',
                'naira_amount' => number_format((float) ($trade->naira_amount ?? 0), 2),
                'pending_minutes' => now()->diffInMinutes($trade->created_at),
            ]);
        }

        foreach ($sellTrades as $trade) {
            $cacheKey = 'escalate:sell:' . $trade->id . ':' . $trade->status;
            if (! $alerts->shouldEscalate($cacheKey)) {
                continue;
            }

            $count++;
            $alerts->sendEscalationAlert('sell', [
                'reference' => $trade->transaction_ref ?? ('SELL-' . $trade->id),
                'user_name' => $trade->user->name ?? $trade->name ?? 'N/A',
                'naira_amount' => number_format((float) ($trade->naira_amount ?? $trade->amount ?? 0), 2),
                'pending_minutes' => now()->diffInMinutes($trade->created_at),
            ]);
        }

        $this->info('Escalation scan complete. Escalated: ' . $count);
        return self::SUCCESS;
    }
}
