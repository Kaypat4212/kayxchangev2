<?php

namespace App\Console\Commands;

use App\Services\RateNotificationService;
use Illuminate\Console\Command;

class SendRateUpdateNotifications extends Command
{
    protected $signature   = 'rates:notify {--context=scheduled : admin_update or scheduled}';
    protected $description = 'Send current crypto rate update notifications to all opted-in users via Telegram and email.';

    public function handle(RateNotificationService $service): int
    {
        $context = $this->option('context');
        $this->info("Sending rate notifications (context: {$context}) ...");

        $service->notifyAllUsers($context);

        $this->info('Done.');
        return Command::SUCCESS;
    }
}
