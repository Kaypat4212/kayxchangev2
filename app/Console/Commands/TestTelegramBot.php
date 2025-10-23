<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TelegramService;

class TestTelegramBot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Telegram bot connection';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Telegram Bot Connection...');
        
        try {
            $telegramService = new TelegramService();
            $result = $telegramService->getBotInfo();
            
            if ($result && $result['ok']) {
                $botInfo = $result['result'];
                $this->info('✅ Bot connection successful!');
                $this->info('Bot ID: ' . $botInfo['id']);
                $this->info('Bot Name: ' . $botInfo['first_name']);
                $this->info('Bot Username: @' . ($botInfo['username'] ?? 'N/A'));
                $this->info('Is Bot: ' . ($botInfo['is_bot'] ? 'Yes' : 'No'));
                
                return 0;
            } else {
                $this->error('❌ Bot connection failed!');
                $this->error('Response: ' . json_encode($result));
                return 1;
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Error connecting to bot:');
            $this->error($e->getMessage());
            return 1;
        }
    }
}
