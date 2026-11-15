<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\TelegramUser;

class TelegramBotController extends Controller
{
    private $botToken;
    private $botUsername;

    public function __construct()
    {
        $this->botToken = env('TELEGRAM_BOT_TOKEN');
        $this->botUsername = env('TELEGRAM_BOT_USERNAME', 'KayXchangeBot');
    }

    /**
     * Handle incoming webhook from Telegram
     */
    public function webhook(Request $request)
    {
        try {
            $update = $request->all();
            Log::info('Telegram webhook received', [
                'update_id' => $update['update_id'] ?? null,
                'has_message' => isset($update['message']),
                'has_callback' => isset($update['callback_query']),
                'chat_id' => $update['message']['chat']['id'] ?? $update['callback_query']['message']['chat']['id'] ?? null,
            ]);

            if (isset($update['message'])) {
                $this->handleMessage($update['message']);
            } elseif (isset($update['callback_query'])) {
                $this->handleCallbackQuery($update['callback_query']);
            }

            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            Log::error('Telegram webhook error', [
                'error' => $e->getMessage(),
                'update_id' => $request->input('update_id'),
            ]);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Handle text messages from users
     */
    private function handleMessage($message)
    {
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';
        $username = $message['from']['username'] ?? null;
        $firstName = $message['from']['first_name'] ?? 'User';

        // Handle commands
        if (str_starts_with($text, '/')) {
            $this->handleCommand($chatId, $text, $username, $firstName);
        } else {
            // Handle regular text (like email verification)
            $this->handleRegularMessage($chatId, $text, $username, $firstName);
        }
    }

    /**
     * Handle bot commands
     */
    private function handleCommand($chatId, $command, $username, $firstName)
    {
        $commandParts = explode(' ', $command);
        $cmd = $commandParts[0];

        switch ($cmd) {
            case '/start':
                $this->handleStartCommand($chatId, $username, $firstName);
                break;
            case '/verify':
                $this->handleVerifyCommand($chatId, $username, $firstName);
                break;
            case '/status':
                $this->handleStatusCommand($chatId, $username);
                break;
            case '/help':
                $this->handleHelpCommand($chatId);
                break;
            case '/features':
                $this->handleFeaturesCommand($chatId);
                break;
            case '/unlink':
                $this->handleUnlinkCommand($chatId, $username);
                break;
            default:
                $this->sendMessage($chatId, "Unknown command. Type /help to see available commands.");
        }
    }

    /**
     * Handle /start command
     */
    private function handleStartCommand($chatId, $username, $firstName)
    {
        $message = "🚀 *Welcome to KayXchange Bot!*\n\n";
        $message .= "Hello {$firstName}! 👋\n\n";
        $message .= "I'm here to send you important notifications about:\n";
        $message .= "• ✅ Trade confirmations\n";
        $message .= "• 💰 Rate updates\n";
        $message .= "• 🔒 Security alerts\n";
        $message .= "• 📈 Account activity\n\n";
        $message .= "*To get started:*\n";
        $message .= "1️⃣ Type `/verify` to link your account\n";
        $message .= "2️⃣ Enter your KayXchange email\n";
        $message .= "3️⃣ Start receiving notifications!\n\n";
        $message .= "Use the buttons below for crypto deposits, your USD crypto portfolio and other platform services. Wallet actions continue on the secure website and require sign-in.\n\n";
        $message .= "_Type /help for more commands_";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '🔗 Link Account', 'callback_data' => 'verify_account'],
                    ['text' => '❓ Help', 'callback_data' => 'show_help']
                ],
                [
                    ['text' => '₿ Crypto Deposit', 'url' => url('/wallet/deposit')],
                    ['text' => '💵 USD Wallet', 'url' => url('/wallet/usd')],
                ],
                [
                    ['text' => '👛 Wallets', 'url' => url('/wallet')],
                    ['text' => '🔁 Swap Crypto', 'url' => url('/swap')],
                ],
                [
                    ['text' => '🧾 Bills', 'url' => url('/bills')],
                    ['text' => '🎁 Gift Cards', 'url' => url('/gift-cards')],
                ],
                [
                    ['text' => '📈 Portfolio', 'url' => url('/portfolio')],
                    ['text' => '🛟 Support', 'url' => url('/support/cases')],
                ],
                [
                    ['text' => '📜 Transactions', 'url' => url('/transactions/history')],
                    ['text' => '🔔 Notifications', 'url' => url('/notifications')],
                ]
            ]
        ];

        $this->sendMessage($chatId, $message, $keyboard);
    }

    /**
     * Handle /verify command
     */
    private function handleVerifyCommand($chatId, $username, $firstName)
    {
        // Check if already verified
        $telegramUser = TelegramUser::where('chat_id', $chatId)->first();
        
        if ($telegramUser && $telegramUser->is_verified) {
            $user = $telegramUser->user;
            $message = "✅ *Account Already Linked*\n\n";
            $message .= "Your Telegram is already connected to:\n";
            $message .= "📧 Email: {$user->email}\n";
            $message .= "👤 Name: {$user->name}\n\n";
            $message .= "You're all set to receive notifications! 🎉";
            
            $this->sendMessage($chatId, $message);
            return;
        }

        $message = "🔗 *Link Your KayXchange Account*\n\n";
        $message .= "Please send me your KayXchange email address to verify your account.\n\n";
        $message .= "📧 *Example:* john@example.com\n\n";
        $message .= "_Make sure it's the same email you used to register on KayXchange._";

        // Store pending verification state
        TelegramUser::updateOrCreate(
            ['chat_id' => $chatId],
            [
                'username' => $username,
                'first_name' => $firstName,
                'is_verified' => false,
                'verification_step' => 'awaiting_email'
            ]
        );

        $this->sendMessage($chatId, $message);
    }

    /**
     * Handle regular messages (email verification)
     */
    private function handleRegularMessage($chatId, $text, $username, $firstName)
    {
        $telegramUser = TelegramUser::where('chat_id', $chatId)->first();

        if (!$telegramUser || $telegramUser->verification_step !== 'awaiting_email') {
            $message = "I don't understand. Type /start to begin or /help for available commands.";
            $this->sendMessage($chatId, $message);
            return;
        }

        // Validate email format
        if (!filter_var($text, FILTER_VALIDATE_EMAIL)) {
            $message = "❌ *Invalid Email Format*\n\n";
            $message .= "Please enter a valid email address.\n";
            $message .= "📧 *Example:* john@example.com";
            $this->sendMessage($chatId, $message);
            return;
        }

        // Find user by email
        $user = User::where('email', $text)->first();

        if (!$user) {
            $message = "❌ *Email Not Found*\n\n";
            $message .= "No KayXchange account found with email: `{$text}`\n\n";
            $message .= "Please:\n";
            $message .= "• Double-check your email spelling\n";
            $message .= "• Make sure you have a KayXchange account\n";
            $message .= "• Try again with the correct email";
            $this->sendMessage($chatId, $message);
            return;
        }

        // Link accounts
        $telegramUser->update([
            'user_id' => $user->id,
            'is_verified' => true,
            'verification_step' => 'completed',
            'verified_at' => now()
        ]);

        // Update user's telegram settings
        $user->update([
            'telegram_username' => $username,
            'telegram_notifications' => true
        ]);

        $message = "✅ *Account Successfully Linked!*\n\n";
        $message .= "🎉 Congratulations {$firstName}!\n\n";
        $message .= "Your Telegram is now connected to:\n";
        $message .= "📧 Email: {$user->email}\n";
        $message .= "👤 Name: {$user->name}\n\n";
        $message .= "You'll now receive notifications for:\n";
        $message .= "• Trade confirmations ✅\n";
        $message .= "• Rate updates 📈\n";
        $message .= "• Security alerts 🔒\n";
        $message .= "• Account activity 💼\n\n";
        $message .= "_You can type /status anytime to check your connection_";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '📊 Visit Dashboard', 'url' => url('/dashboard')],
                    ['text' => '⚙️ Settings', 'url' => url('/settings/telegram')]
                ]
            ]
        ];

        $this->sendMessage($chatId, $message, $keyboard);

        // Log the successful verification
        Log::info('Telegram account verified', [
            'user_id' => $user->id,
            'chat_id' => $chatId,
            'username' => $username
        ]);
    }

    /**
     * Handle callback queries (inline keyboard buttons)
     */
    private function handleCallbackQuery($callbackQuery)
    {
        $chatId = $callbackQuery['message']['chat']['id'];
        $data = $callbackQuery['data'];
        $messageId = $callbackQuery['message']['message_id'];

        switch ($data) {
            case 'verify_account':
                $this->handleVerifyCommand($chatId, $callbackQuery['from']['username'] ?? null, $callbackQuery['from']['first_name'] ?? 'User');
                break;
            case 'show_help':
                $this->handleHelpCommand($chatId);
                break;
        }

        // Answer callback query
        $this->answerCallbackQuery($callbackQuery['id']);
    }

    /**
     * Send a message to Telegram
     */
    public function sendMessage($chatId, $text, $keyboard = null)
    {
        if (!$this->botToken) {
            Log::error('Telegram bot token not configured');
            return false;
        }

        $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";
        
        $data = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown'
        ];

        if ($keyboard) {
            $data['reply_markup'] = json_encode($keyboard);
        }

        try {
            $response = Http::post($url, $data);
            $result = $response->json();

            // Names, references and other user supplied values may contain
            // Markdown characters. Retry malformed Markdown messages as plain
            // text rather than silently losing the bot response.
            if (!$response->successful()
                && stripos((string) ($result['description'] ?? ''), "can't parse entities") !== false) {
                unset($data['parse_mode']);
                $response = Http::post($url, $data);
                $result = $response->json();
                if ($response->successful() && ($result['ok'] ?? false)) {
                    Log::warning('Telegram bot message delivered as plain text after Markdown parsing failed.', ['chat_id' => $chatId]);
                    return true;
                }
            }
            
            if ($response->successful() && ($result['ok'] ?? false)) {
                Log::info('Telegram message sent successfully', [
                    'chat_id' => $chatId,
                    'message_length' => strlen($text)
                ]);
                return true;
            } else {
                Log::error('Failed to send Telegram message', [
                    'chat_id' => $chatId,
                    'response' => $response->json()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Telegram API error', [
                'error' => $e->getMessage(),
                'chat_id' => $chatId
            ]);
            return false;
        }
    }

    /**
     * Answer callback query
     */
    private function answerCallbackQuery($callbackQueryId, $text = null)
    {
        $url = "https://api.telegram.org/bot{$this->botToken}/answerCallbackQuery";
        
        $data = ['callback_query_id' => $callbackQueryId];
        
        if ($text) {
            $data['text'] = $text;
        }

        Http::post($url, $data);
    }

    /**
     * Send notification to user
     */
    public static function sendNotificationToUser($user, $message, $keyboard = null)
    {
        try {
            $telegramUser = TelegramUser::where('user_id', $user->id)
                ->where('is_verified', true)
                ->first();

            if (!$telegramUser) {
                Log::info('User does not have verified Telegram account', [
                    'user_id' => $user->id
                ]);
                return false;
            }

            $controller = new self();
            return $controller->sendMessage($telegramUser->chat_id, $message, $keyboard);
        } catch (\Exception $e) {
            Log::error('Failed to send Telegram notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Handle /status command
     */
    private function handleStatusCommand($chatId, $username)
    {
        $telegramUser = TelegramUser::where('chat_id', $chatId)->first();

        if (!$telegramUser || !$telegramUser->is_verified) {
            $message = "❌ *Account Not Linked*\n\n";
            $message .= "Your Telegram is not connected to any KayXchange account.\n\n";
            $message .= "Type /verify to link your account.";
            $this->sendMessage($chatId, $message);
            return;
        }

        $user = $telegramUser->user;
        $message = "✅ *Account Status*\n\n";
        $message .= "📧 Email: {$user->email}\n";
        $message .= "👤 Name: {$user->name}\n";
        $message .= "🔗 Linked: " . $telegramUser->verified_at->diffForHumans() . "\n";
        $message .= "🔔 Notifications: " . ($user->telegram_notifications ? 'Enabled' : 'Disabled') . "\n\n";
        $message .= "Everything looks good! 🎉";

        $this->sendMessage($chatId, $message);
    }

    /**
     * Handle /help command
     */
    private function handleHelpCommand($chatId)
    {
        $message = "📚 *KayXchange Bot Help*\n\n";
        $message .= "*Available Commands:*\n";
        $message .= "/start - Welcome message and setup\n";
        $message .= "/verify - Link your KayXchange account\n";
        $message .= "/status - Check your account status\n";
        $message .= "/help - Show this help message\n";
        $message .= "/unlink - Disconnect your account\n\n";
        $message .= "Platform access: Crypto deposits, USD crypto portfolio, wallet history, swaps, bills, gift cards, portfolio tracking, transactions, notifications and support are available on the secure KayXchange website. Use /start or /features for quick links.\n\n";
        $message .= "*What I can do:*\n";
        $message .= "• Send trade confirmations\n";
        $message .= "• Notify about rate changes\n";
        $message .= "• Send security alerts\n";
        $message .= "• Keep you updated on account activity\n\n";
        $message .= "*Need more help?*\n";
        $message .= "Contact KayXchange support at support@kayxchange.com";

        $this->sendMessage($chatId, $message);
    }

    private function handleFeaturesCommand($chatId): void
    {
        $message = "✨ *KayXchange services*\nChoose a service. Sign in on the secure site when asked; sensitive wallet actions are completed there.";
        $keyboard = ['inline_keyboard' => [
            [['text' => '₿ Crypto deposit', 'url' => url('/wallet/deposit')], ['text' => '💵 USD crypto wallet', 'url' => url('/wallet/usd')]],
            [['text' => '👛 Wallets', 'url' => url('/wallet')], ['text' => '📜 Wallet history', 'url' => url('/wallet/history')]],
            [['text' => '🔁 Swap crypto', 'url' => url('/swap')], ['text' => '💸 Withdraw', 'url' => url('/wallet/withdraw')]],
            [['text' => '🎁 Gift cards', 'url' => url('/gift-cards')], ['text' => '🧾 Bills', 'url' => url('/bills')]],
            [['text' => '📈 Portfolio', 'url' => url('/portfolio')], ['text' => '🧾 Transactions', 'url' => url('/transactions/history')]],
            [['text' => '🔔 Notifications', 'url' => url('/notifications')], ['text' => '🛟 Support', 'url' => url('/support/cases')]],
            [['text' => '📊 Rates', 'url' => url('/rate-calculator')], ['text' => '🏠 Dashboard', 'url' => url('/dashboard')]],
        ]];
        $this->sendMessage($chatId, $message, $keyboard);
    }

    /**
     * Handle /unlink command
     */
    private function handleUnlinkCommand($chatId, $username)
    {
        $telegramUser = TelegramUser::where('chat_id', $chatId)->first();

        if (!$telegramUser || !$telegramUser->is_verified) {
            $message = "❌ No linked account found to unlink.";
            $this->sendMessage($chatId, $message);
            return;
        }

        // Unlink account
        $user = $telegramUser->user;
        $user->update([
            'telegram_username' => null,
            'telegram_notifications' => false
        ]);

        $telegramUser->delete();

        $message = "✅ *Account Unlinked*\n\n";
        $message .= "Your Telegram has been disconnected from your KayXchange account.\n\n";
        $message .= "You will no longer receive notifications.\n\n";
        $message .= "Type /verify anytime to link again.";

        $this->sendMessage($chatId, $message);

        Log::info('Telegram account unlinked', [
            'user_id' => $user->id,
            'chat_id' => $chatId
        ]);
    }

    /**
     * Set webhook URL
     */
    public function setWebhook(Request $request)
    {
        if (!$this->botToken) {
            return response()->json(['error' => 'Bot token not configured'], 400);
        }

        $webhookUrl = url('/api/telegram/webhook');
        $url = "https://api.telegram.org/bot{$this->botToken}/setWebhook";

        try {
            $response = Http::post($url, [
                'url' => $webhookUrl,
                'allowed_updates' => ['message', 'callback_query']
            ]);

            return response()->json([
                'success' => $response->successful(),
                'webhook_url' => $webhookUrl,
                'response' => $response->json()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
