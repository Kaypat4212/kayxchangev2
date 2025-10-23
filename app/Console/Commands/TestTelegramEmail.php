<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TelegramService;
use App\Models\User;

class TestTelegramEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:test-email {email} {--chat-id=123456789} {--username=testuser}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Telegram email verification process';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $chatId = $this->option('chat-id');
        $username = $this->option('username');

        $this->info("Testing email verification for: {$email}");
        $this->info("Chat ID: {$chatId}");
        $this->info("Username: {$username}");

        // Check if user exists
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("❌ User with email {$email} not found in database");
            return 1;
        }

        $this->info("✅ User found: {$user->name} (ID: {$user->id})");

        // Test the email verification process
        try {
            $telegramService = new TelegramService();
            
            // Simulate the webhook update
            $update = [
                'message' => [
                    'chat' => [
                        'id' => $chatId,
                        'first_name' => 'Test User'
                    ],
                    'from' => [
                        'username' => $username
                    ],
                    'text' => $email
                ]
            ];

            $this->info("Simulating webhook update...");
            $result = $telegramService->processUpdate($update);

            if ($result) {
                $this->info("✅ Email verification process completed successfully!");
                
                // Check if user was updated
                $user->refresh();
                $this->info("Updated user data:");
                $this->info("  - Telegram Username: " . ($user->telegram_username ?? 'Not set'));
                $this->info("  - Telegram Chat ID: " . ($user->telegram_chat_id ?? 'Not set'));
                $this->info("  - Telegram Verified: " . ($user->telegram_verified ? 'Yes' : 'No'));
                
                return 0;
            } else {
                $this->error("❌ Email verification process failed");
                return 1;
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Error during verification: " . $e->getMessage());
            return 1;
        }
    }
}
