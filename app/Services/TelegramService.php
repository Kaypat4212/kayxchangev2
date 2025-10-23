<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class TelegramService
{
    private $botToken;
    private $apiUrl;
    private $lastUpdateId = 0;

    public function __construct()
    {
        $this->botToken = env('KAYXCHANGE_TELEGRAM_BOT_TOKEN');
        $this->apiUrl = "https://api.telegram.org/bot{$this->botToken}";
        $this->loadLastUpdateId();
    }

    /**
     * Load the last processed update ID for polling
     */
    private function loadLastUpdateId()
    {
        $this->lastUpdateId = (int) cache('telegram_last_update_id', 0);
    }

    /**
     * Save the last processed update ID
     */
    private function saveLastUpdateId($updateId)
    {
        $this->lastUpdateId = $updateId;
        cache(['telegram_last_update_id' => $updateId], now()->addDays(7));
    }

    /**
     * Check if we're in production mode (has public domain)
     */
    public function isProductionMode()
    {
        $appUrl = env('APP_URL', '');
        return !str_contains($appUrl, 'localhost') && 
               !str_contains($appUrl, '127.0.0.1') && 
               !str_contains($appUrl, '192.168.') &&
               !str_contains($appUrl, ':8000');
    }

    /**
     * Poll for updates (for local development)
     */
    public function pollForUpdates()
    {
        if ($this->isProductionMode()) {
            Log::info('Skipping polling - production mode detected');
            return false;
        }

        try {
            $response = Http::get("{$this->apiUrl}/getUpdates", [
                'offset' => $this->lastUpdateId + 1,
                'limit' => 10,
                'timeout' => 30,
            ]);

            $result = $response->json();

            if ($response->successful() && $result['ok']) {
                $updates = $result['result'] ?? [];
                
                foreach ($updates as $update) {
                    $this->processUpdate($update);
                    $this->saveLastUpdateId($update['update_id']);
                }

                if (!empty($updates)) {
                    Log::info('Processed ' . count($updates) . ' Telegram updates via polling');
                }

                return true;
            } else {
                Log::error('Failed to poll Telegram updates', [
                    'error' => $result['description'] ?? 'Unknown error'
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Exception polling Telegram updates', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send a message to a specific chat ID
     */
    public function sendMessage($chatId, $message, $parseMode = 'Markdown')
    {
        try {
            $response = Http::post("{$this->apiUrl}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => $parseMode,
                'disable_web_page_preview' => true,
            ]);

            $result = $response->json();

            if ($response->successful() && $result['ok']) {
                Log::info('Telegram message sent successfully', [
                    'chat_id' => $chatId,
                    'message_id' => $result['result']['message_id'],
                ]);
                return $result;
            } else {
                Log::error('Failed to send Telegram message', [
                    'chat_id' => $chatId,
                    'error' => $result['description'] ?? 'Unknown error',
                    'error_code' => $result['error_code'] ?? null,
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Exception sending Telegram message', [
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send message to user by their database record
     */
    public function sendMessageToUser(User $user, $message)
    {
        // If user has completed bot verification, send via chat_id
        if ($user->telegram_chat_id && $user->telegram_notifications && $user->telegram_verified) {
            return $this->sendMessage($user->telegram_chat_id, $message);
        }
        
        // If user only has username but notifications enabled, try to send a setup reminder
        if ($user->telegram_username && $user->telegram_notifications && !$user->telegram_verified) {
            Log::info('User has username but not verified - they need to complete bot setup', [
                'user_id' => $user->id,
                'telegram_username' => $user->telegram_username,
            ]);
            
            // We can't send direct messages without chat_id, but we can log for admin awareness
            return false;
        }

        Log::info('User not configured for Telegram notifications', [
            'user_id' => $user->id,
            'has_chat_id' => !empty($user->telegram_chat_id),
            'has_username' => !empty($user->telegram_username),
            'notifications_enabled' => $user->telegram_notifications,
            'telegram_verified' => $user->telegram_verified,
        ]);
        
        return false;
    }

    /**
     * Check if user can receive notifications
     */
    public function canSendToUser(User $user)
    {
        return $user->telegram_chat_id && 
               $user->telegram_notifications && 
               $user->telegram_verified;
    }

    /**
     * Get bot information
     */
    public function getBotInfo()
    {
        try {
            $response = Http::get("{$this->apiUrl}/getMe");
            return $response->json();
        } catch (\Exception $e) {
            Log::error('Failed to get bot info', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Set webhook for receiving updates (production only)
     */
    public function setWebhook($webhookUrl)
    {
        if (!$this->isProductionMode()) {
            Log::info('Webhook setup skipped - local development mode detected');
            return [
                'ok' => false,
                'description' => 'Webhook setup is disabled in local development. Use polling instead.'
            ];
        }

        try {
            $response = Http::post("{$this->apiUrl}/setWebhook", [
                'url' => $webhookUrl,
                'allowed_updates' => ['message', 'callback_query'],
            ]);

            $result = $response->json();

            if ($response->successful() && $result['ok']) {
                Log::info('Telegram webhook set successfully', [
                    'webhook_url' => $webhookUrl
                ]);
            } else {
                Log::error('Failed to set Telegram webhook', [
                    'error' => $result['description'] ?? 'Unknown error',
                    'webhook_url' => $webhookUrl
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Exception setting webhook', [
                'error' => $e->getMessage(),
                'webhook_url' => $webhookUrl
            ]);
            return false;
        }
    }

    /**
     * Remove webhook and use polling (for local development)
     */
    public function enablePolling()
    {
        try {
            // Remove webhook first
            $response = Http::get("{$this->apiUrl}/deleteWebhook");
            $result = $response->json();
            
            if ($result['ok']) {
                Log::info('Webhook removed, polling enabled');
                return true;
            } else {
                Log::error('Failed to remove webhook', ['error' => $result]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Exception enabling polling', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Process incoming webhook updates
     */
    public function processUpdate($update)
    {
        try {
            if (isset($update['message'])) {
                $message = $update['message'];
                $chatId = $message['chat']['id'];
                $text = $message['text'] ?? '';
                $firstName = $message['from']['first_name'] ?? '';
                $username = $message['from']['username'] ?? '';

                Log::info('Received Telegram message', [
                    'chat_id' => $chatId,
                    'username' => $username,
                    'text' => $text,
                ]);

                // Handle different commands
                if (str_starts_with($text, '/start')) {
                    $this->handleStartCommand($chatId, $firstName);
                } elseif (str_starts_with($text, '/verify')) {
                    $this->handleVerifyCommand($chatId, $text, $username);
                } elseif (str_starts_with($text, '/help')) {
                    $this->handleHelpCommand($chatId);
                } elseif (filter_var($text, FILTER_VALIDATE_EMAIL)) {
                    // User sent an email for verification
                    $this->handleEmailVerification($chatId, $text, $username);
                } else {
                    $this->handleUnknownCommand($chatId);
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Error processing Telegram update', [
                'error' => $e->getMessage(),
                'update' => $update,
            ]);
            return false;
        }
    }

    /**
     * Handle /start command
     */
    private function handleStartCommand($chatId, $firstName)
    {
        $message = "🚀 *Welcome to KayXchange, {$firstName}!*\n\n" .
                  "I'm your personal KayXchange notification bot. I'll keep you updated on:\n\n" .
                  "💰 Trade confirmations\n" .
                  "📈 Rate updates\n" .
                  "🔐 Security alerts\n" .
                  "📊 Account activities\n\n" .
                  "*To get started:*\n" .
                  "1️⃣ Send me your KayXchange email address\n" .
                  "2️⃣ I'll verify your account\n" .
                  "3️⃣ Enable notifications in your settings\n\n" .
                  "Type your email address to begin verification! 📧";

        $this->sendMessage($chatId, $message);
    }

    /**
     * Handle email verification
     */
    private function handleEmailVerification($chatId, $email, $username)
    {
        $user = User::where('email', $email)->first();

        if ($user) {
            // Check if already verified with different chat_id
            if ($user->telegram_chat_id && $user->telegram_chat_id != $chatId) {
                $message = "⚠️ *Account Already Linked*\n\n" .
                          "This email is already linked to another Telegram account.\n\n" .
                          "If this is your account, please contact support or unlink the previous connection in your KayXchange settings.";
                
                $this->sendMessage($chatId, $message);
                return;
            }

            // Update user with chat_id and mark as verified
            $user->update([
                'telegram_chat_id' => $chatId,
                'telegram_username' => $username,
                'telegram_verified' => true,
            ]);

            // Also check if there are other users with this username who need linking
            $this->linkUsernameBasedAccounts($username, $chatId);

            $message = "✅ *Account Verified Successfully!*\n\n" .
                      "Hello *{$user->name}*!\n\n" .
                      "Your KayXchange account has been successfully linked to this Telegram chat.\n\n" .
                      "*Account Details:*\n" .
                      "📧 Email: {$user->email}\n" .
                      "💰 Balance: ₦" . number_format($user->balance, 2) . "\n" .
                      "🔐 KYC Status: " . ($user->kyc_verified ? 'Verified ✅' : 'Pending ⏳') . "\n\n" .
                      "*Next Steps:*\n" .
                      "• Go to Settings → Telegram Notifications\n" .
                      "• Enable the notification toggle\n" .
                      "• Test your setup\n\n" .
                      "You're all set! 🎉";

            $this->sendMessage($chatId, $message);

            Log::info('User verified Telegram account', [
                'user_id' => $user->id,
                'email' => $user->email,
                'chat_id' => $chatId,
                'username' => $username,
            ]);
        } else {
            $message = "❌ *Email Not Found*\n\n" .
                      "The email address `{$email}` is not registered with KayXchange.\n\n" .
                      "*Please:*\n" .
                      "• Check for typos in your email\n" .
                      "• Make sure you have a KayXchange account\n" .
                      "• Create an account at kayxchange.net if needed\n\n" .
                      "Try again with the correct email address! 📧";

            $this->sendMessage($chatId, $message);
        }
    }

    /**
     * Handle /help command
     */
    private function handleHelpCommand($chatId)
    {
        $message = "🆘 *KayXchange Bot Help*\n\n" .
                  "*Available Commands:*\n" .
                  "/start - Start the bot and get welcome message\n" .
                  "/help - Show this help message\n" .
                  "/verify - Check your verification status\n\n" .
                  "*How to Setup:*\n" .
                  "1️⃣ Send your KayXchange email address\n" .
                  "2️⃣ Bot will verify your account\n" .
                  "3️⃣ Go to Settings → Telegram in your KayXchange account\n" .
                  "4️⃣ Enable notifications\n\n" .
                  "*Supported Notifications:*\n" .
                  "💰 Trade completions\n" .
                  "📈 Rate updates\n" .
                  "🔐 Security alerts\n" .
                  "💳 Withdrawal confirmations\n\n" .
                  "Need more help? Contact support at admin@kayxchange.net";

        $this->sendMessage($chatId, $message);
    }

    /**
     * Handle /verify command
     */
    private function handleVerifyCommand($chatId, $text, $username)
    {
        $user = User::where('telegram_chat_id', $chatId)->first();

        if ($user) {
            $message = "✅ *Verification Status*\n\n" .
                      "*Account:* {$user->name}\n" .
                      "*Email:* {$user->email}\n" .
                      "*Verified:* " . ($user->telegram_verified ? 'Yes ✅' : 'No ❌') . "\n" .
                      "*Notifications:* " . ($user->telegram_notifications ? 'Enabled 🔔' : 'Disabled 🔕') . "\n" .
                      "*Balance:* ₦" . number_format($user->balance, 2) . "\n\n" .
                      ($user->telegram_notifications ? 
                        "All set! You'll receive notifications here. 🎉" : 
                        "⚠️ Enable notifications in your KayXchange settings to receive updates.");

            $this->sendMessage($chatId, $message);
        } else {
            $message = "❌ *Not Verified*\n\n" .
                      "Your Telegram account is not linked to any KayXchange account.\n\n" .
                      "Send me your KayXchange email address to get started! 📧";

            $this->sendMessage($chatId, $message);
        }
    }

    /**
     * Handle unknown commands
     */
    private function handleUnknownCommand($chatId)
    {
        $message = "🤔 *I didn't understand that command.*\n\n" .
                  "Try one of these:\n" .
                  "• Send me your email address for verification\n" .
                  "• Type /help for available commands\n" .
                  "• Type /start to begin setup\n\n" .
                  "How can I help you today? 😊";

        $this->sendMessage($chatId, $message);
    }

    /**
     * Send trade completion notification
     */
    public function notifyTradeComplete(User $user, $trade)
    {
        $tradeType = ucfirst($trade->type ?? 'Trade');
        $amount = number_format($trade->amount_ngn ?? 0, 2);
        $coin = $trade->coin ?? 'Cryptocurrency';

        $message = "💰 *{$tradeType} Completed - KayXchange*\n\n" .
                  "Hello *{$user->name}*!\n\n" .
                  "Great news! Your {$tradeType} has been completed successfully.\n\n" .
                  "📊 *Trade Details:*\n" .
                  "• Type: {$tradeType}\n" .
                  "• Asset: {$coin}\n" .
                  "• Amount: ₦{$amount}\n" .
                  "• Status: Completed ✅\n" .
                  "• Time: " . now()->format('Y-m-d H:i:s') . "\n\n" .
                  "💳 Your account balance has been updated.\n\n" .
                  "Thank you for using KayXchange! 🚀";

        return $this->sendMessageToUser($user, $message);
    }

    /**
     * Send security alert notification
     */
    public function notifySecurityAlert(User $user, $alertType, $details = [])
    {
        $message = "🚨 *Security Alert - KayXchange*\n\n" .
                  "Hello *{$user->name}*!\n\n" .
                  "We detected important activity on your account:\n\n" .
                  "⚠️ *Alert Type:* {$alertType}\n" .
                  "🕐 *Time:* " . now()->format('Y-m-d H:i:s') . "\n" .
                  "🌐 *IP Address:* " . (request()->ip() ?? 'Unknown') . "\n";

        if (!empty($details)) {
            $message .= "\n📋 *Additional Details:*\n";
            foreach ($details as $key => $value) {
                $message .= "• {$key}: {$value}\n";
            }
        }

        $message .= "\n🔒 *Security Tips:*\n" .
                   "• If this wasn't you, change your password immediately\n" .
                   "• Enable 2FA for extra security\n" .
                   "• Contact support if you need help\n\n" .
                   "Stay safe! 🛡️";

        return $this->sendMessageToUser($user, $message);
    }

    /**
     * Send rate update notification
     */
    public function notifyRateUpdate(User $user, $coins)
    {
        $coinList = is_array($coins) ? implode(', ', $coins) : $coins;

        $message = "📈 *Rate Update Alert - KayXchange*\n\n" .
                  "Hello *{$user->name}*!\n\n" .
                  "Cryptocurrency rates have been updated:\n\n" .
                  "🪙 *Updated Coins:*\n{$coinList}\n\n" .
                  "💡 *Quick Actions:*\n" .
                  "• Check new rates on the platform\n" .
                  "• Place trades at current rates\n" .
                  "• Set up rate alerts for better timing\n\n" .
                  "🕐 *Updated:* " . now()->format('Y-m-d H:i:s') . "\n\n" .
                  "Visit KayXchange to see the latest rates! 🚀";

        return $this->sendMessageToUser($user, $message);
    }

    /**
     * Send withdrawal notification
     */
    public function notifyWithdrawal(User $user, $withdrawal)
    {
        $amount = number_format($withdrawal->amount ?? 0, 2);
        $status = ucfirst($withdrawal->status ?? 'pending');

        $statusEmoji = match($withdrawal->status ?? 'pending') {
            'completed' => '✅',
            'cancelled' => '❌', 
            'rejected' => '🚫',
            default => '⏳'
        };

        $message = "💳 *Withdrawal {$status} - KayXchange*\n\n" .
                  "Hello *{$user->name}*!\n\n" .
                  "Your withdrawal request has been {$status}.\n\n" .
                  "💰 *Withdrawal Details:*\n" .
                  "• Amount: ₦{$amount}\n" .
                  "• Status: {$status} {$statusEmoji}\n" .
                  "• Reference: #{$withdrawal->id}\n" .
                  "• Time: " . now()->format('Y-m-d H:i:s') . "\n\n";

        if ($withdrawal->status === 'completed') {
            $message .= "✅ Funds have been sent to your bank account.\n" .
                       "Please allow 1-24 hours for bank processing.\n\n";
        } elseif (in_array($withdrawal->status, ['cancelled', 'rejected'])) {
            $message .= "💰 The amount has been refunded to your account balance.\n\n";
        } else {
            $message .= "⏳ Your withdrawal is being processed.\n" .
                       "You'll be notified when it's completed.\n\n";
        }

        $message .= "Thank you for using KayXchange! 💙";

        return $this->sendMessageToUser($user, $message);
    }

    /**
     * Send test notification
     */
    public function sendTestNotification(User $user)
    {
        $message = "🔔 *Test Notification - KayXchange*\n\n" .
                  "Hello *{$user->name}*!\n\n" .
                  "This is a test message to confirm your Telegram notifications are working perfectly! ✅\n\n" .
                  "🎉 *Setup Complete!*\n" .
                  "Your notifications are now active and you'll receive updates about:\n\n" .
                  "💰 Trade confirmations\n" .
                  "📈 Rate updates\n" .
                  "🔐 Security alerts\n" .
                  "💳 Withdrawal updates\n" .
                  "📊 Account activities\n\n" .
                  "🚀 You're all set to trade with confidence!\n\n" .
                  "_Test sent at: " . now()->format('Y-m-d H:i:s') . "_";

        return $this->sendMessageToUser($user, $message);
    }

    /**
     * Remove webhook
     */
    public function removeWebhook()
    {
        $url = "https://api.telegram.org/bot{$this->botToken}/deleteWebhook";

        try {
            $response = Http::get($url);
            return $response->json();
        } catch (\Exception $e) {
            Log::error('Failed to remove Telegram webhook', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Link accounts that have username but no chat_id
     */
    private function linkUsernameBasedAccounts($username, $chatId)
    {
        if (!$username) return;

        // Find users who have this username but no chat_id (username-only setup)
        $usersToLink = User::where('telegram_username', $username)
            ->whereNull('telegram_chat_id')
            ->where('telegram_verified', false)
            ->get();

        foreach ($usersToLink as $user) {
            $user->update([
                'telegram_chat_id' => $chatId,
                'telegram_verified' => true,
            ]);

            Log::info('Linked username-based account to chat', [
                'user_id' => $user->id,
                'username' => $username,
                'chat_id' => $chatId,
            ]);

            // Send notification to newly linked account
            $message = "🔗 *Account Automatically Linked!*\n\n" .
                      "Hello *{$user->name}*!\n\n" .
                      "Since you had already added @{$username} to your KayXchange settings, " .
                      "I've automatically linked your account!\n\n" .
                      "Your notifications are now fully active. 🎉";

            $this->sendMessage($chatId, $message);
        }
    }
}