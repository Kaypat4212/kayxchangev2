<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TelegramService;

class TelegramPollUpdates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:poll {--continuous : Run continuously in background}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Poll Telegram for updates (for local development)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $telegramService = new TelegramService();
        
        // Check if we should run in production mode
        if ($telegramService->isProductionMode()) {
            $this->error('❌ This command is for local development only.');
            $this->info('💡 In production, use webhooks instead.');
            return 1;
        }

        $this->info('🤖 Starting Telegram polling for local development...');
        $this->info('📧 You can now send your email to @TradewithkayxchangeBOT');
        
        $continuous = $this->option('continuous');
        $pollCount = 0;

        do {
            try {
                $result = $telegramService->pollForUpdates();

                // Webhook is active — stop the poller automatically
                if ($result === 'webhook_conflict') {
                    $this->error('🚫 A webhook is currently active. Polling cannot run alongside a webhook.');
                    $this->warn('👉 To use polling: visit /api/telegram/setup-webhook?delete=1 to remove it first.');
                    $this->warn('👉 Or just leave the webhook running — it handles all messages automatically.');
                    return 1;
                }

                $pollCount++;
                
                if ($pollCount % 10 == 0) {
                    $this->info("📊 Polling cycle: {$pollCount}");
                }
                
                if ($continuous) {
                    sleep(2); // Poll every 2 seconds
                } else {
                    // Single poll
                    break;
                }
                
                // Allow graceful shutdown with Ctrl+C
                if (function_exists('pcntl_signal_dispatch')) {
                    pcntl_signal_dispatch();
                }
                
            } catch (\Exception $e) {
                $this->error('❌ Error: ' . $e->getMessage());
                
                if ($continuous) {
                    $this->warn('⏳ Retrying in 5 seconds...');
                    sleep(5);
                } else {
                    return 1;
                }
            }
            
        } while ($continuous);

        if (!$continuous) {
            $this->info('✅ Single poll completed');
        }
        
        return 0;
    }
}
