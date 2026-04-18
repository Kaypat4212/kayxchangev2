<?php

namespace App\Console\Commands;

use App\Models\SellTrade;
use App\Services\CryptoMonitorService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class MonitorPendingSellTrades extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitor:sell-trades
                            {--hours=24 : Only check trades created within the last N hours}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Poll blockchain APIs for pending sell trades and auto-approve confirmed receipts';

    public function handle(CryptoMonitorService $monitor): int
    {
        $hours  = (int) $this->option('hours');
        $cutoff = Carbon::now()->subHours($hours);

        $trades = SellTrade::where('status', 'pending')
            ->whereIn('coin', ['BTC', 'ETH', 'USDT', 'SOL'])
            ->where('created_at', '>=', $cutoff)
            ->get();

        if ($trades->isEmpty()) {
            $this->info('[CryptoMonitor] No pending trades to check.');
            return self::SUCCESS;
        }

        $this->info("[CryptoMonitor] Checking {$trades->count()} pending trade(s)…");

        $approved = 0;

        foreach ($trades as $trade) {
            $result = $monitor->checkTrade($trade);
            if ($result) {
                $approved++;
                $this->line("  ✔  Trade #{$trade->id} ({$trade->coin}) auto-approved.");
            }
        }

        $this->info("[CryptoMonitor] Done. Auto-approved: {$approved} / {$trades->count()}");

        return self::SUCCESS;
    }
}
