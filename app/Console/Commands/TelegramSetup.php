<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TelegramService;

class TelegramSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup Telegram bot for current environment';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🤖 Setting up Telegram bot...');
        
        $telegramService = new TelegramService();
        
        // First, test bot connection
        $botInfo = $telegramService->getBotInfo();
        
        if (!$botInfo || !$botInfo['ok']) {
            $this->error('❌ Cannot connect to Telegram bot. Check your bot token.');
            return 1;
        }
        
        $bot = $botInfo['result'];
        $this->info("✅ Connected to bot: {$bot['first_name']} (@{$bot['username']})");
        
        if ($telegramService->isProductionMode()) {
            $this->info('🌐 Production mode detected - setting up webhook...');
            
            $webhookUrl = env('APP_URL') . '/api/telegram/webhook';
            $result = $telegramService->setWebhook($webhookUrl);
            
            if ($result['ok']) {
                $this->info("✅ Webhook set successfully: {$webhookUrl}");
            } else {
                $this->error("❌ Failed to set webhook: " . ($result['description'] ?? 'Unknown error'));
                return 1;
            }
            
        } else {
            $this->info('🏠 Local development mode detected - enabling polling...');
            
            if ($telegramService->enablePolling()) {
                $this->info('✅ Polling enabled successfully');
                $this->warn('💡 Run "php artisan telegram:poll --continuous" to start receiving messages');
            } else {
                $this->error('❌ Failed to enable polling');
                return 1;
            }
        }
        
        $this->info('🎉 Telegram bot setup completed!');
        $this->info('📱 Users can now chat with @' . $bot['username']);
        
        return 0;
    }
}
