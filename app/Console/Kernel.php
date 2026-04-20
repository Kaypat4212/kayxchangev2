<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Only poll for updates in local development
        if (app()->environment('local')) {
            $schedule->command('telegram:poll')
                ->everyMinute()
                ->withoutOverlapping()
                ->runInBackground();
        }

        // Auto-detect confirmed crypto payments for pending sell trades
        $schedule->command('monitor:sell-trades')
            ->everyTwoMinutes()
            ->withoutOverlapping()
            ->runInBackground();

        // Auto-escalate pending trades that exceed SLA threshold
        $schedule->command('trades:escalate-pending')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->runInBackground();

        // Daily backup at 2:00 AM — DB dump + storage, notify admin
        $schedule->command('backup:run --notify')
            ->dailyAt('02:00')
            ->withoutOverlapping()
            ->runInBackground();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
