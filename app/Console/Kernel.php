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

        // Check price alerts every 5 minutes
        $schedule->command('alerts:check')
            ->everyFiveMinutes()
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

        // Rate update notifications — 8:00 AM WAT (07:00 UTC) every day
        $schedule->command('rates:notify --context=scheduled')
            ->dailyAt('07:00')
            ->timezone('UTC')
            ->withoutOverlapping()
            ->runInBackground();

        // Rate update notifications — 1:00 PM WAT (12:00 UTC) every day
        $schedule->command('rates:notify --context=scheduled')
            ->dailyAt('12:00')
            ->timezone('UTC')
            ->withoutOverlapping()
            ->runInBackground();

        // Daily trade summary digest — 8:00 AM WAT (07:00 UTC)
        $schedule->command('digest:daily')
            ->dailyAt('07:00')
            ->timezone('UTC')
            ->withoutOverlapping()
            ->runInBackground();

        // Weekly KYC expiry reminders — every Monday 08:00 UTC
        $schedule->command('kyc:expiry-reminders')
            ->weeklyOn(1, '08:00')
            ->timezone('UTC')
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
