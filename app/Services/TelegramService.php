<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\SellTrade;
use App\Models\BuyTrade;
use App\Models\Kyc;
use App\Models\CryptoRate;
use App\Models\TelegramBotMessage;

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

    // ─────────────────────────── State machine helpers ──────────────────────────

    private function getState(int $chatId): ?string
    {
        return Cache::get("tg_state_{$chatId}");
    }

    private function setState(int $chatId, string $state): void
    {
        Cache::put("tg_state_{$chatId}", $state, now()->addHour());
    }

    private function clearState(int $chatId): void
    {
        Cache::forget("tg_state_{$chatId}");
        Cache::forget("tg_data_{$chatId}");
    }

    private function getData(int $chatId): array
    {
        return Cache::get("tg_data_{$chatId}", []);
    }

    private function setData(int $chatId, array $data): void
    {
        Cache::put("tg_data_{$chatId}", $data, now()->addHour());
    }

    private function mergeData(int $chatId, array $merge): void
    {
        $this->setData($chatId, array_merge($this->getData($chatId), $merge));
    }

    // ─────────────────────── Message logging & owner forwarding ─────────────────

    /**
     * Save every incoming user message to DB and forward a copy to the bot owner.
     */
    private function logAndForwardMessage(
        int $chatId, string $username, string $firstName,
        string $text, string $type, ?string $state, bool $isCommand,
        ?string $fileId = null, ?string $fileName = null
    ): void {
        try {
            // Resolve linked user if any
            $linkedUser = User::where('telegram_chat_id', $chatId)->first();

            TelegramBotMessage::create([
                'chat_id'      => $chatId,
                'username'     => $username ?: null,
                'first_name'   => $firstName ?: null,
                'user_id'      => $linkedUser?->id,
                'message_text' => mb_substr($text, 0, 2000),
                'message_type' => $type,
                'file_id'      => $fileId,
                'file_name'    => $fileName,
                'state_at_time'=> $state,
                'is_command'   => $isCommand,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log Telegram message', ['error' => $e->getMessage()]);
        }

        // Forward to bot owner chat ID
        $ownerChatId = env('TELEGRAM_OWNER_CHAT_ID');
        if (!$ownerChatId || (string)$ownerChatId === (string)$chatId) {
            return; // no owner set, or owner sent their own message
        }

        try {
            $sender = $firstName ?: ($username ? "@{$username}" : "Chat #{$chatId}");
            $stateLabel = $state ? " _(state: {$state})_" : '';
            $typeIcon = match($type) {
                'photo'    => '📸',
                'command'  => '⌨️',
                'sticker'  => '🎭',
                'document' => '📄',
                'video'    => '🎥',
                'audio'    => '🎵',
                'voice'    => '🎤',
                default    => '💬',
            };

            $forward = "{$typeIcon} *User message*\n" .
                       "👤 {$sender}" . ($username ? " (@{$username})" : '') . "\n" .
                       "🆔 Chat ID: `{$chatId}`\n" .
                       ($linkedUser ? "🔗 Account: {$linkedUser->name} ({$linkedUser->email})\n" : "🔗 _Not linked to any account_\n") .
                       "🕐 " . now()->format('d M Y, H:i:s') . $stateLabel . "\n\n" .
                       "```\n{$text}\n```";

            $this->sendMessage((int)$ownerChatId, $forward, 'Markdown');
        } catch (\Exception $e) {
            Log::error('Failed to forward message to bot owner', ['error' => $e->getMessage()]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────────

    /**
     * Check if we're in production mode (has public domain)
     */
    /**
     * Returns the URL to use for Telegram button links.
     * Reads TELEGRAM_APP_URL first, falls back to APP_URL.
     * Set TELEGRAM_APP_URL in .env to support multiple domains or override per environment.
     */
    private function telegramAppUrl(): string
    {
        return rtrim(env('TELEGRAM_APP_URL', env('APP_URL', 'https://kayxchange.com')), '/');
    }

    /**
     * Whether to use url-type inline buttons.
     * True for any public HTTPS address (localhost and private IPs get callback_data fallback).
     * ngrok, staging, and real domains all get real URL buttons.
     */
    private function shouldUseUrlButtons(): bool
    {
        $url = $this->telegramAppUrl();
        if (!str_starts_with($url, 'https://')) {
            return false;
        }
        return !str_contains($url, 'localhost')
            && !str_contains($url, '127.0.0.1')
            && !str_contains($url, '192.168.');
    }

    /**
     * Whether this instance is running on a real server (not local dev).
     * Used to decide between polling and webhook mode.
     * ngrok is treated as local so polling keeps working during ngrok sessions.
     */
    public function isProductionMode()
    {
        $appUrl = env('APP_URL', '');
        return !str_contains($appUrl, 'localhost') &&
               !str_contains($appUrl, '127.0.0.1') &&
               !str_contains($appUrl, '192.168.') &&
               !str_contains($appUrl, ':8000') &&
               !str_contains($appUrl, 'ngrok');
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
                $description = $result['description'] ?? 'Unknown error';

                // Webhook is active — polling cannot run alongside it
                if (str_contains($description, 'webhook is active') || str_contains($description, 'terminated by setWebhook')) {
                    Log::warning('Polling stopped: webhook is active. Use deleteWebhook to switch back to polling.');
                    return 'webhook_conflict';
                }

                Log::error('Failed to poll Telegram updates', [
                    'error' => $description
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
    public function sendMessage($chatId, $message, $parseMode = 'Markdown', $keyboard = null)
    {
        try {
            $payload = [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => $parseMode,
                'disable_web_page_preview' => true,
            ];

            if ($keyboard !== null) {
                $payload['reply_markup'] = json_encode($keyboard);
            }

            $response = Http::post("{$this->apiUrl}/sendMessage", $payload);

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
     * Send a message to all configured admin Telegram chats.
     */
    public function sendToAdminChats(string $message, ?array $keyboard = null, string $parseMode = 'Markdown'): int
    {
        $chatIds = [];

        foreach (['TELEGRAM_CHAT_ID', 'KAYXCHANGE_TELEGRAM_CHAT_ID', 'TELEGRAM_OWNER_CHAT_ID'] as $envKey) {
            $envChatId = env($envKey);
            if (!empty($envChatId)) {
                $chatIds[] = (string) $envChatId;
            }
        }

        $adminChatIds = User::where('is_admin', true)
            ->whereNotNull('telegram_chat_id')
            ->pluck('telegram_chat_id')
            ->map(fn ($id) => (string) $id)
            ->all();

        $chatIds = array_values(array_unique(array_filter(array_merge($chatIds, $adminChatIds))));

        $sent = 0;
        foreach ($chatIds as $chatId) {
            if ($this->sendMessage($chatId, $message, $parseMode, $keyboard)) {
                $sent++;
            }
        }

        return $sent;
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
        // Allow webhook on any public HTTPS URL (ngrok, staging, real domain).
        // Only block non-HTTPS or localhost.
        if (!str_starts_with($webhookUrl, 'https://')) {
            return [
                'ok' => false,
                'description' => 'Webhook requires an HTTPS URL. Use polling for local HTTP development.',
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
    /**
     * Resolve the canonical command keyword from raw message text.
     * Handles slash-prefixed (/start), plain text (start), and bot-mention variants (/start@BotName).
     * Returns lowercase command string e.g. "start", or null if not a command.
     */
    private function resolveCommand(string $text): ?string
    {
        $text = trim($text);

        // Strip optional leading slash
        if (str_starts_with($text, '/')) {
            $text = ltrim($text, '/');
        }

        // Strip @BotName suffix (e.g. /start@MyBot)
        if (str_contains($text, '@')) {
            $text = explode('@', $text)[0];
        }

        // Extract the first word (command without arguments)
        $command = strtolower(explode(' ', $text)[0]);

        $known = ['start', 'register', 'rates', 'buy', 'sell', 'balance', 'trades', 'verify', 'help', 'cancel'];

        return in_array($command, $known, true) ? $command : null;
    }

    public function processUpdate($update)
    {
        try {
            if (isset($update['message'])) {
                $message   = $update['message'];
                $chatId    = (int) $message['chat']['id'];
                $text      = $message['text'] ?? '';
                $firstName = $message['from']['first_name'] ?? '';
                $username  = $message['from']['username'] ?? '';

                Log::info('Received Telegram message', [
                    'chat_id'  => $chatId,
                    'username' => $username,
                    'text'     => substr($text, 0, 100),
                ]);

                // ── Detect message type and extract media info ─────────────────
                $isCommand   = !empty($text) && str_starts_with(trim($text), '/');
                $logFileId   = null;
                $logFileName = null;
                if (!empty($message['photo'])) {
                    $messageType = 'photo';
                    $logFileId   = end($message['photo'])['file_id'];
                    $text        = $text ?: '[photo]';
                } elseif (!empty($message['sticker'])) {
                    $messageType = 'sticker';
                    $logFileId   = $message['sticker']['file_id'] ?? null;
                    $logFileName = $message['sticker']['set_name'] ?? null;
                    $text        = $message['sticker']['emoji'] ?? '[sticker]';
                } elseif (!empty($message['document'])) {
                    $messageType = 'document';
                    $logFileId   = $message['document']['file_id'] ?? null;
                    $logFileName = $message['document']['file_name'] ?? null;
                    $text        = $message['document']['file_name'] ?? '[document]';
                } elseif (!empty($message['video'])) {
                    $messageType = 'video';
                    $logFileId   = $message['video']['file_id'] ?? null;
                    $logFileName = $message['video']['file_name'] ?? null;
                    $text        = $text ?: '[video]';
                } elseif (!empty($message['audio'])) {
                    $messageType = 'audio';
                    $logFileId   = $message['audio']['file_id'] ?? null;
                    $logFileName = $message['audio']['file_name'] ?? $message['audio']['title'] ?? null;
                    $text        = $text ?: '[audio]';
                } elseif (!empty($message['voice'])) {
                    $messageType = 'voice';
                    $logFileId   = $message['voice']['file_id'] ?? null;
                    $text        = $text ?: '[voice message]';
                } elseif ($isCommand) {
                    $messageType = 'command';
                } else {
                    $messageType = 'text';
                }

                // ── Log to DB and forward to bot owner ────────────────────────
                $this->logAndForwardMessage(
                    $chatId, $username, $firstName,
                    $text ?: '[unknown]',
                    $messageType, $this->getState($chatId), $isCommand, $logFileId, $logFileName
                );

                // ── Photo upload (proof) ──────────────────────────────────────────
                if (!empty($message['photo'])) {
                    $state = $this->getState($chatId);
                    $photos = $message['photo'];
                    // Take the largest photo size (last element)
                    $fileId = $logFileId ?? end($photos)['file_id'];
                    if ($state === 'sell_proof') {
                        $this->handleSellProofUpload($chatId, $fileId);
                    } elseif ($state === 'buy_proof') {
                        $this->handleBuyProofUpload($chatId, $fileId);
                    } else {
                        $this->sendMessage($chatId, "📸 Photo received, but I'm not expecting one right now. Use /sell or /buy to start a trade.");
                    }
                    return true;
                }

                // ── Document sent as file (remind to send as photo) ──────────────
                if (!empty($message['document'])) {
                    $state = $this->getState($chatId);
                    if (in_array($state, ['sell_proof', 'buy_proof'])) {
                        $this->sendMessage($chatId,
                            "⚠️ Please send the screenshot as a *photo* (not a file/document).\n\nOn Telegram: tap the paperclip → Photo & Video → choose your screenshot.");
                    }
                    return true;
                }

                // ── Commands take priority over active states ─────────────────────
                $command = $this->resolveCommand($text);
                if ($command !== null) {
                    if ($command === 'cancel') {
                        $this->handleCancelCommand($chatId);
                        return true;
                    }
                    match ($command) {
                        'start'    => $this->handleStartCommand($chatId, $firstName),
                        'register' => $this->handleRegisterCommand($chatId),
                        'rates'    => $this->handleRatesCommand($chatId),
                        'buy'      => $this->handleBuyCommand($chatId),
                        'sell'     => $this->handleSellCommand($chatId),
                        'balance'  => $this->handleBalanceCommand($chatId),
                        'trades'   => $this->handleTradesCommand($chatId),
                        'verify'   => $this->handleVerifyCommand($chatId, $text, $username),
                        'help'     => $this->handleHelpCommand($chatId),
                        default    => $this->handleUnknownCommand($chatId),
                    };
                    return true;
                }

                // ── State-aware text input ────────────────────────────────────────
                $state = $this->getState($chatId);
                if ($state !== null) {
                    switch ($state) {
                        case 'sell_amount':
                            $this->handleSellAmountInput($chatId, $text);
                            return true;
                        case 'buy_amount':
                            $this->handleBuyAmountInput($chatId, $text);
                            return true;
                        case 'buy_wallet':
                            $this->handleBuyWalletInput($chatId, $text);
                            return true;
                        case 'link_pin':
                            $this->handleLinkPinInput($chatId, $text);
                            return true;
                        case 'reg_set_pin':
                            $this->handleRegSetPinInput($chatId, $text);
                            return true;
                        case 'reg_confirm_pin':
                            $this->handleRegConfirmPinInput($chatId, $text);
                            return true;
                        case 'sell_pin':
                            $this->handleSellPinInput($chatId, $text);
                            return true;
                        case 'buy_pin':
                            $this->handleBuyPinInput($chatId, $text);
                            return true;
                        case 'reg_name':
                            $this->handleRegNameInput($chatId, $text);
                            return true;
                        case 'reg_email':
                            $this->handleRegEmailInput($chatId, $text);
                            return true;
                        case 'reg_password':
                            $this->handleRegPasswordInput($chatId, $text);
                            return true;
                        case 'reg_confirm_password':
                            $this->handleRegConfirmPasswordInput($chatId, $text);
                            return true;
                    }
                }

                // ── Email address for account linking ─────────────────────────────
                if (filter_var($text, FILTER_VALIDATE_EMAIL)) {
                    $this->handleEmailVerification($chatId, $text, $username);
                    return true;
                }

                // ── Unknown ───────────────────────────────────────────────────────
                $this->handleUnknownCommand($chatId);
            }

            if (isset($update['callback_query'])) {
                $cb        = $update['callback_query'];
                $chatId    = (int) $cb['message']['chat']['id'];
                $messageId = $cb['message']['message_id'] ?? null;
                $data      = $cb['data'] ?? '';
                $firstName = $cb['from']['first_name'] ?? '';

                // Dismiss the loading spinner on the button
                Http::post("{$this->apiUrl}/answerCallbackQuery", [
                    'callback_query_id' => $cb['id'],
                ]);

                // ── Cancel ────────────────────────────────────────────────────────
                if ($data === 'cancel') {
                    $this->handleCancelCommand($chatId);
                    return true;
                }

                // ── Sell flow callbacks ────────────────────────────────────────────
                if (str_starts_with($data, 'sell_coin:')) {
                    $this->handleSellCoinSelected($chatId, substr($data, 10));
                    return true;
                }
                if (str_starts_with($data, 'sell_network:')) {
                    $this->handleSellNetworkSelected($chatId, substr($data, 13));
                    return true;
                }
                if (str_starts_with($data, 'sell_payout:')) {
                    $this->handleSellPayoutSelected($chatId, substr($data, 12));
                    return true;
                }
                if ($data === 'sell_confirm') {
                    $this->handleSellConfirm($chatId);
                    return true;
                }

                // ── Buy flow callbacks ─────────────────────────────────────────────
                if (str_starts_with($data, 'buy_coin:')) {
                    $this->handleBuyCoinSelected($chatId, substr($data, 9));
                    return true;
                }
                if (str_starts_with($data, 'buy_network:')) {
                    $this->handleBuyNetworkSelected($chatId, substr($data, 12));
                    return true;
                }
                if ($data === 'buy_confirm') {
                    $this->handleBuyConfirm($chatId);
                    return true;
                }

                // ── Admin approve/reject callbacks ─────────────────────────────────
                if (str_starts_with($data, 'approve_sell:')) {
                    $this->handleAdminApproveSell($chatId, (int) substr($data, 13), $messageId);
                    return true;
                }
                if (str_starts_with($data, 'reject_sell:')) {
                    $this->handleAdminRejectSell($chatId, (int) substr($data, 12), $messageId);
                    return true;
                }
                if (str_starts_with($data, 'approve_buy:')) {
                    $this->handleAdminApproveBuy($chatId, (int) substr($data, 12), $messageId);
                    return true;
                }
                if (str_starts_with($data, 'reject_buy:')) {
                    $this->handleAdminRejectBuy($chatId, (int) substr($data, 11), $messageId);
                    return true;
                }
                if (str_starts_with($data, 'approve_kyc:')) {
                    $this->handleAdminApproveKyc($chatId, (int) substr($data, 12), $messageId);
                    return true;
                }
                if (str_starts_with($data, 'reject_kyc:')) {
                    $this->handleAdminRejectKyc($chatId, (int) substr($data, 11), $messageId);
                    return true;
                }
                if (str_starts_with($data, 'approve_withdrawal:')) {
                    $this->handleAdminApproveWithdrawal($chatId, (int) substr($data, 19), $messageId);
                    return true;
                }
                if (str_starts_with($data, 'reject_withdrawal:')) {
                    $this->handleAdminRejectWithdrawal($chatId, (int) substr($data, 18), $messageId);
                    return true;
                }

                // ── Legacy command callbacks ───────────────────────────────────────
                match ($data) {
                    'cmd_start'    => $this->handleStartCommand($chatId, $firstName),
                    'cmd_register' => $this->handleRegisterCommand($chatId),
                    'cmd_rates'    => $this->handleRatesCommand($chatId),
                    'cmd_buy'      => $this->handleBuyCommand($chatId),
                    'cmd_sell'     => $this->handleSellCommand($chatId),
                    'cmd_balance'  => $this->handleBalanceCommand($chatId),
                    'cmd_trades'   => $this->handleTradesCommand($chatId),
                    'cmd_help'     => $this->handleHelpCommand($chatId),
                    default        => null,
                };
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
     * Build a keyboard button — uses 'url' when TELEGRAM_APP_URL / APP_URL is a public HTTPS address,
     * falls back to 'callback_data' for localhost / private IPs.
     * Works with ngrok, staging, and real domains automatically.
     */
    private function linkButton(string $text, string $path, string $callbackFallback): array
    {
        if ($this->shouldUseUrlButtons()) {
            return ['text' => $text, 'url' => $this->telegramAppUrl() . $path];
        }

        return ['text' => $text, 'callback_data' => $callbackFallback];
    }

    /**
     * Handle /start command
     */
    private function handleStartCommand($chatId, $firstName)
    {
        $appUrl = $this->telegramAppUrl();
        $user   = User::where('telegram_chat_id', $chatId)->first();

        // ── Returning user: personalised dashboard ────────────────────────────
        if ($user) {
            $kycStatus  = $user->kyc_verified ? 'Verified ✅' : 'Pending ⏳';
            $notifStatus = $user->telegram_notifications ? 'On 🔔' : 'Off 🔕';

            $message = "👋 *Welcome back, {$user->name}!*\n\n" .
                       "💰 Balance: *₦" . number_format($user->balance, 2) . "*\n" .
                       "🔐 KYC: {$kycStatus}\n" .
                       "🔔 Notifications: {$notifStatus}\n\n" .
                       "*What would you like to do?*\n" .
                       "• `/sell` — Sell cryptocurrency\n" .
                       "• `/buy` — Buy cryptocurrency\n" .
                       "• `/rates` — View live rates\n" .
                       "• `/trades` — Your recent trades\n" .
                       "• `/balance` — Check balance\n\n" .
                       "_" . now()->format('d M Y, H:i') . "_";

            $keyboard = [
                'inline_keyboard' => [
                    [
                        $this->linkButton('💸 Buy Crypto', '/buy', 'cmd_buy'),
                        $this->linkButton('💵 Sell Crypto', '/sell', 'cmd_sell'),
                    ],
                    [
                        ['text' => '📊 Live Rates',   'callback_data' => 'cmd_rates'],
                        ['text' => '💰 My Balance',   'callback_data' => 'cmd_balance'],
                    ],
                    [
                        ['text' => '📋 My Trades',    'callback_data' => 'cmd_trades'],
                        $this->linkButton('🌐 Dashboard', '/dashboard', 'cmd_start'),
                    ],
                ],
            ];

            $this->sendMessage($chatId, $message, 'Markdown', $keyboard);
            return;
        }

        // ── New user: onboarding / registration prompt ────────────────────────
        $message = "🚀 *Welcome to KayXchange, {$firstName}!*\n\n" .
                   "The fastest way to buy & sell cryptocurrency in Nigeria.\n\n" .
                   "━━━━━━━━━━━━━━━━━\n" .
                   "🆕 *New here?*\n" .
                   "Tap *Register* below to create your account in seconds — no browser needed!\n\n" .
                   "🔗 *Already have an account?*\n" .
                   "Send me your KayXchange email address and I'll link it.\n" .
                   "━━━━━━━━━━━━━━━━━\n\n" .
                   "🌐 {$appUrl}";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '🆕 Create Account', 'callback_data' => 'cmd_register'],
                ],
                [
                    $this->linkButton('🌐 Open Website', '', 'cmd_start'),
                    ['text' => '📊 Live Rates', 'callback_data' => 'cmd_rates'],
                ],
                [
                    ['text' => '❓ Help', 'callback_data' => 'cmd_help'],
                ],
            ],
        ];

        $this->sendMessage($chatId, $message, 'Markdown', $keyboard);
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

            // PIN gate — require PIN before linking if user has one set
            if ($user->transaction_pin) {
                $this->clearState($chatId);
                $this->setState($chatId, 'link_pin');
                $this->setData($chatId, [
                    'pending_email'    => $email,
                    'pending_username' => $username,
                    'pending_user_id'  => $user->id,
                ]);
                $this->sendMessage($chatId,
                    "🔐 *Verify It's You*\n\n" .
                    "This account (*{$user->name}*) has a transaction PIN set.\n" .
                    "Enter your 4-digit PIN to link your Telegram.\n\n" .
                    "_Type /cancel to abort._");
                return;
            }

            // No PIN set — link directly
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

    // ─────────────────────── PIN helpers & handlers ──────────────────────────────

    /**
     * Shared PIN verification against the user record.
     * Handles lockout and attempt counting — mirrors PinController logic.
     */
    private function verifyPinForUser(User $user, string $pin): array
    {
        if (!$user->transaction_pin) {
            return ['ok' => true]; // No PIN set — treat as pass-through
        }

        // Locked?
        if ($user->pin_locked_until && \Carbon\Carbon::now()->lt($user->pin_locked_until)) {
            $remaining = (int) \Carbon\Carbon::now()->diffInMinutes($user->pin_locked_until, false);
            return ['ok' => false, 'message' => "🔒 PIN locked. Try again in {$remaining} minute(s).", 'locked' => true];
        }

        if (Hash::check($pin, $user->transaction_pin)) {
            $user->pin_attempts     = 0;
            $user->pin_locked_until = null;
            $user->save();
            return ['ok' => true];
        }

        $user->pin_attempts = ($user->pin_attempts ?? 0) + 1;

        if ($user->pin_attempts >= 5) {
            $user->pin_locked_until = \Carbon\Carbon::now()->addMinutes(15);
            $user->save();
            return ['ok' => false, 'message' => "❌ Incorrect PIN. Too many attempts — PIN locked for 15 minutes.", 'locked' => true];
        }

        $remaining = 5 - $user->pin_attempts;
        $user->save();
        return ['ok' => false, 'message' => "❌ Incorrect PIN. {$remaining} attempt(s) remaining.", 'locked' => false];
    }

    /**
     * PIN entry when linking an existing account via email.
     */
    private function handleLinkPinInput(int $chatId, string $text): void
    {
        $pin  = trim($text);
        $data = $this->getData($chatId);

        $userId = $data['pending_user_id'] ?? null;
        $user   = $userId ? User::find($userId) : null;

        if (!$user) {
            $this->clearState($chatId);
            $this->sendMessage($chatId, "❌ Session expired. Please send your email again to restart.");
            return;
        }

        if (!preg_match('/^\d{4}$/', $pin)) {
            $this->sendMessage($chatId, "⚠️ PIN must be exactly 4 digits. Try again.");
            return;
        }

        $result = $this->verifyPinForUser($user, $pin);

        if (!$result['ok']) {
            $this->sendMessage($chatId, $result['message']);
            if ($result['locked'] ?? false) {
                $this->clearState($chatId);
            }
            return;
        }

        // PIN verified — complete the linking
        $email    = $data['pending_email']    ?? '';
        $username = $data['pending_username'] ?? '';

        $user->update([
            'telegram_chat_id'  => $chatId,
            'telegram_username' => $username,
            'telegram_verified' => true,
        ]);

        $this->linkUsernameBasedAccounts($username, $chatId);
        $this->clearState($chatId);

        $this->sendMessage($chatId,
            "✅ *Account Linked Successfully!*\n\n" .
            "Hello *{$user->name}*! Your Telegram is now linked.\n\n" .
            "📧 Email: {$email}\n" .
            "💰 Balance: ₦" . number_format($user->balance, 2) . "\n" .
            "🔐 KYC: " . ($user->kyc_verified ? 'Verified ✅' : 'Pending ⏳') . "\n\n" .
            "Use /buy, /sell, /rates or /balance to get started! 🎉");

        Log::info('User linked Telegram via PIN', ['user_id' => $user->id, 'chat_id' => $chatId]);
    }

    /**
     * PIN entry before a sell trade.
     */
    private function handleSellPinInput(int $chatId, string $text): void
    {
        $pin  = trim($text);
        $data = $this->getData($chatId);

        $user = User::find($data['user_id'] ?? 0);
        if (!$user) {
            $this->clearState($chatId);
            $this->sendMessage($chatId, "❌ Session expired. Please type /sell again.");
            return;
        }

        if (!preg_match('/^\d{4}$/', $pin)) {
            $this->sendMessage($chatId, "⚠️ PIN must be exactly 4 digits. Try again.");
            return;
        }

        $result = $this->verifyPinForUser($user, $pin);

        if (!$result['ok']) {
            $this->sendMessage($chatId, $result['message']);
            if ($result['locked'] ?? false) {
                $this->clearState($chatId);
            }
            return;
        }

        // PIN correct — start the sell flow
        $this->setState($chatId, 'sell_coin');
        $this->setData($chatId, ['user_id' => $user->id]);

        $rates = CryptoRate::whereIn('coin', ['BTC', 'ETH', 'USDT', 'SOL'])->get()->keyBy('coin');
        $msg = "✅ *PIN verified!*\n\n💵 *Sell Cryptocurrency*\n\n*Live Sell Rates:*\n";
        foreach (['BTC', 'ETH', 'USDT', 'SOL'] as $c) {
            $r = isset($rates[$c]) ? number_format($rates[$c]->sell_rate, 2) : 'N/A';
            $msg .= "• *{$c}*: ₦{$r}/USD\n";
        }
        $msg .= "\nChoose the coin you want to sell:";

        $keyboard = ['inline_keyboard' => [
            [
                ['text' => '₿ BTC',   'callback_data' => 'sell_coin:BTC'],
                ['text' => 'Ξ ETH',   'callback_data' => 'sell_coin:ETH'],
                ['text' => '💲 USDT', 'callback_data' => 'sell_coin:USDT'],
                ['text' => '◎ SOL',   'callback_data' => 'sell_coin:SOL'],
            ],
            [['text' => '❌ Cancel', 'callback_data' => 'cancel']],
        ]];

        $this->sendMessage($chatId, $msg, 'Markdown', $keyboard);
    }

    /**
     * PIN entry before a buy trade.
     */
    private function handleBuyPinInput(int $chatId, string $text): void
    {
        $pin  = trim($text);
        $data = $this->getData($chatId);

        $user = User::find($data['user_id'] ?? 0);
        if (!$user) {
            $this->clearState($chatId);
            $this->sendMessage($chatId, "❌ Session expired. Please type /buy again.");
            return;
        }

        if (!preg_match('/^\d{4}$/', $pin)) {
            $this->sendMessage($chatId, "⚠️ PIN must be exactly 4 digits. Try again.");
            return;
        }

        $result = $this->verifyPinForUser($user, $pin);

        if (!$result['ok']) {
            $this->sendMessage($chatId, $result['message']);
            if ($result['locked'] ?? false) {
                $this->clearState($chatId);
            }
            return;
        }

        // PIN correct — start the buy flow
        $this->setState($chatId, 'buy_coin');
        $this->setData($chatId, ['user_id' => $user->id]);

        $rates = CryptoRate::whereIn('coin', ['BTC', 'ETH', 'USDT', 'SOL'])->get()->keyBy('coin');
        $msg = "✅ *PIN verified!*\n\n💸 *Buy Cryptocurrency*\n\n*Live Buy Rates:*\n";
        foreach (['BTC', 'ETH', 'USDT', 'SOL'] as $c) {
            $r = isset($rates[$c]) ? number_format($rates[$c]->buy_rate, 2) : 'N/A';
            $msg .= "• *{$c}*: ₦{$r}/USD\n";
        }
        $msg .= "\nChoose the coin you want to buy:";

        $keyboard = ['inline_keyboard' => [
            [
                ['text' => '₿ BTC',   'callback_data' => 'buy_coin:BTC'],
                ['text' => 'Ξ ETH',   'callback_data' => 'buy_coin:ETH'],
                ['text' => '💲 USDT', 'callback_data' => 'buy_coin:USDT'],
                ['text' => '◎ SOL',   'callback_data' => 'buy_coin:SOL'],
            ],
            [['text' => '❌ Cancel', 'callback_data' => 'cancel']],
        ]];

        $this->sendMessage($chatId, $msg, 'Markdown', $keyboard);
    }

    /**
     * Registration Step 5 — first PIN entry.
     */
    private function handleRegSetPinInput(int $chatId, string $text): void
    {
        $pin = trim($text);

        if (!preg_match('/^\d{4}$/', $pin)) {
            $this->sendMessage($chatId, "⚠️ PIN must be exactly 4 digits (numbers only). Try again.");
            return;
        }

        $this->mergeData($chatId, ['reg_pin' => $pin]);
        $this->setState($chatId, 'reg_confirm_pin');

        $this->sendMessage($chatId,
            "🔐 *Confirm your PIN*\n\n" .
            "Re-enter your 4-digit PIN to confirm.\n\n" .
            "⚠️ *Delete these messages after sending* for your security.");
    }

    /**
     * Registration Step 5b — confirm PIN + create account.
     */
    private function handleRegConfirmPinInput(int $chatId, string $text): void
    {
        $pin  = trim($text);
        $data = $this->getData($chatId);

        if (!preg_match('/^\d{4}$/', $pin)) {
            $this->sendMessage($chatId, "⚠️ PIN must be exactly 4 digits. Try again.");
            return;
        }

        if ($pin !== ($data['reg_pin'] ?? '')) {
            $this->sendMessage($chatId, "❌ PINs don't match. Please re-enter your PIN.");
            $this->setState($chatId, 'reg_set_pin');
            return;
        }

        try {
            $username = $data['name'] ?? '';

            $user = User::create([
                'name'                   => $username,
                'email'                  => $data['email'],
                'password'               => $data['password'],
                'transaction_pin'        => Hash::make($pin),
                'pin_attempts'           => 0,
                'telegram_chat_id'       => (string) $chatId,
                'telegram_verified'      => true,
                'telegram_notifications' => true,
            ]);

            event(new \Illuminate\Auth\Events\Registered($user));

            $this->clearState($chatId);

            $appUrl = $this->telegramAppUrl();

            $this->sendMessage($chatId,
                "🎉 *Welcome to KayXchange, {$username}!*\n\n" .
                "Your account has been created and linked to this Telegram.\n\n" .
                "📧 Email: {$data['email']}\n" .
                "💰 Balance: ₦0.00\n" .
                "🔐 PIN: Set ✅\n\n" .
                "You can now:\n" .
                "• `/sell` — Sell cryptocurrency\n" .
                "• `/buy` — Buy cryptocurrency\n" .
                "• `/rates` — View live rates\n" .
                "• `/balance` — Check your balance\n\n" .
                "🌐 Login anytime at {$appUrl}\n\n" .
                "_Your PIN is required for every trade. Keep it safe._",
                'Markdown',
                [
                    'inline_keyboard' => [[
                        $this->linkButton('🌐 Go to Dashboard', '/dashboard', 'cmd_start'),
                    ]],
                ]
            );
        } catch (\Exception $e) {
            Log::error('Telegram registration failed', ['error' => $e->getMessage(), 'chat_id' => $chatId]);
            $this->clearState($chatId);
            $this->sendMessage($chatId,
                "❌ Registration failed due to a server error. Please try again or register at the website.");
        }
    }

    // ─────────────────────── Registration flow ──────────────────────────────

    /**
     * Handle /register command — start the registration flow.
     */
    private function handleRegisterCommand(int $chatId): void
    {
        // If already linked, no need to register again
        $existing = User::where('telegram_chat_id', $chatId)->first();
        if ($existing) {
            $this->sendMessage($chatId,
                "✅ *Already Registered!*\n\n" .
                "Your Telegram is linked to *{$existing->name}* ({$existing->email}).\n\n" .
                "Use /balance, /buy, /sell or /rates to get started!");
            return;
        }

        $this->clearState($chatId);
        $this->setState($chatId, 'reg_name');
        $this->setData($chatId, []);

        $this->sendMessage($chatId,
            "👤 *Create a KayXchange Account*\n\n" .
            "Welcome! Let's set up your account in a few quick steps.\n\n" .
            "*Step 1 of 4* — What is your full name?\n\n" .
            "_Type your full name and send it._\n\n" .
            "Type /cancel at any time to stop.");
    }

    /** Step 1 — receive name */
    private function handleRegNameInput(int $chatId, string $text): void
    {
        $name = trim($text);
        if (strlen($name) < 2 || strlen($name) > 255) {
            $this->sendMessage($chatId, "⚠️ Please enter a valid full name (2–255 characters).");
            return;
        }

        $this->mergeData($chatId, ['name' => $name]);
        $this->setState($chatId, 'reg_email');

        $this->sendMessage($chatId,
            "✅ Name saved: *{$name}*\n\n" .
            "*Step 2 of 4* — What is your email address?\n\n" .
            "_Send your email address._");
    }

    /** Step 2 — receive email */
    private function handleRegEmailInput(int $chatId, string $text): void
    {
        $email = strtolower(trim($text));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->sendMessage($chatId, "⚠️ That doesn't look like a valid email. Please try again.");
            return;
        }

        if (User::where('email', $email)->exists()) {
            $this->sendMessage($chatId,
                "❌ That email is already registered.\n\n" .
                "If this is your account, just send me your email to link your Telegram, or use /cancel and visit the website to log in.");
            return;
        }

        $this->mergeData($chatId, ['email' => $email]);
        $this->setState($chatId, 'reg_password');

        $this->sendMessage($chatId,
            "✅ Email saved: *{$email}*\n\n" .
            "*Step 3 of 4* — Choose a password.\n\n" .
            "_Requirements: at least 8 characters, with letters and numbers._\n\n" .
            "⚠️ *Delete this message after sending* for your security.");
    }

    /** Step 3 — receive password */
    private function handleRegPasswordInput(int $chatId, string $text): void
    {
        $password = $text; // do NOT trim — spaces may be intentional

        if (strlen($password) < 8) {
            $this->sendMessage($chatId, "⚠️ Password must be at least 8 characters. Try again.");
            return;
        }
        if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $this->sendMessage($chatId, "⚠️ Password must contain at least one letter and one number. Try again.");
            return;
        }

        // Store hashed — never store plain text
        $this->mergeData($chatId, ['password' => Hash::make($password)]);
        $this->setState($chatId, 'reg_confirm_password');

        $this->sendMessage($chatId,
            "🔐 Password set.\n\n" .
            "*Step 4 of 4* — Please re-enter your password to confirm.\n\n" .
            "⚠️ *Delete this message after sending* for your security.");
    }

    /** Step 4 — confirm password + create account */
    private function handleRegConfirmPasswordInput(int $chatId, string $text): void
    {
        $data = $this->getData($chatId);
        $hashedPassword = $data['password'] ?? null;

        if (!$hashedPassword || !Hash::check($text, $hashedPassword)) {
            $this->sendMessage($chatId,
                "❌ Passwords don't match. Please re-enter your password (the one you chose in step 3).");
            return;
        }

        // Move to PIN setup step
        $this->setState($chatId, 'reg_set_pin');

        $this->sendMessage($chatId,
            "🔐 *Step 5 of 5* — Set a Transaction PIN\n\n" .
            "Choose a *4-digit PIN* you'll use to authorise trades.\n" .
            "_Example: 1234 — but use something only you know._\n\n" .
            "⚠️ *Delete this message after sending* for your security.");
    }

    // ─────────────────────────────────────────────────────────────────────────────

    /**
     * Handle /help command
     */
    private function handleHelpCommand($chatId)
    {
        $user = User::where('telegram_chat_id', $chatId)->first();
        $isLinked = $user !== null;
        $hasPin   = $user && $user->transaction_pin;

        $message = "🆘 *KayXchange Bot — Help*\n\n";

        if (!$isLinked) {
            $message .= "⚠️ *Your Telegram is not linked to any account yet.*\n" .
                        "Use /register to create one or send your email to link an existing account.\n\n";
        }

        $message .= "🔄 *Getting Started*\n" .
                    "`/register` — Create a new KayXchange account right here\n" .
                    "  └ 5-step setup: name → email → password → PIN → done!\n" .
                    "`Email address` — Send your email to link an existing account\n" .
                    "  └ If your account has a PIN, you\'ll be asked to verify it\n\n" .
                    "💱 *Trading Commands*\n" .
                    "`/sell` — Sell cryptocurrency (BTC, ETH, USDT, SOL)\n" .
                    "  └ PIN required if set → choose coin → amount → proof photo → payout\n" .
                    "`/buy` — Buy cryptocurrency\n" .
                    "  └ PIN required if set → choose coin → amount → wallet → proof photo\n" .
                    "`/rates` — View live NGN buy & sell rates\n" .
                    "`/balance` — Check your account balance\n" .
                    "`/trades` — View your last 5 trades\n\n" .
                    "🔒 *Security*\n" .
                    "Transaction PIN is required before every buy or sell trade.\n" .
                    "Wrong PIN 5× = 15-minute lockout. Set/change your PIN on the website.\n\n" .
                    "👤 *Account Commands*\n" .
                    "`/start` — Main menu (personalised when linked)\n" .
                    "`/verify` — Show your account & verification status\n" .
                    "`/cancel` — Cancel any active flow at any time\n" .
                    "`/help` — Show this message\n\n";

        if ($hasPin) {
            $message .= "✅ *Your account has a transaction PIN set.*\n\n";
        } elseif ($isLinked) {
            $message .= "⚠️ *No transaction PIN set.* Visit the website to set one — it\'s required for trades here.\n\n";
        }

        $message .= "📧 Support: admin@kayxchange.net";

        $keyboard = [
            'inline_keyboard' => [
                $isLinked ? [
                    $this->linkButton('💸 Buy', '/buy', 'cmd_buy'),
                    $this->linkButton('💵 Sell', '/sell', 'cmd_sell'),
                    ['text' => '📊 Rates', 'callback_data' => 'cmd_rates'],
                ] : [
                    ['text' => '🆕 Register Now', 'callback_data' => 'cmd_register'],
                    ['text' => '📊 Live Rates',   'callback_data' => 'cmd_rates'],
                ],
            ],
        ];

        $this->sendMessage($chatId, $message, 'Markdown', $keyboard);
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
     * Handle /rates command - show live crypto buy/sell rates
     */
    private function handleRatesCommand($chatId)
    {
        try {
            $rates = \App\Models\CryptoRate::all();

            if ($rates->isEmpty()) {
                $this->sendMessage($chatId, "⚠️ Rates are currently unavailable. Please try again shortly.");
                return;
            }

            $message = "📊 *Live KayXchange Rates*\n\n";
            foreach ($rates as $rate) {
                $coin = strtoupper($rate->coin);
                $buy  = number_format($rate->buy_rate, 2);
                $sell = number_format($rate->sell_rate, 2);
                $message .= "🪙 *{$coin}*\n";
                $message .= "  Buy: ₦{$buy} | Sell: ₦{$sell}\n\n";
            }
            $message .= "_Rates updated: " . now()->format('d M Y, H:i') . "_";

            $keyboard = [
                'inline_keyboard' => [[
                    $this->linkButton('💸 Buy Now', '/buy', 'cmd_buy'),
                    $this->linkButton('💵 Sell Now', '/sell', 'cmd_sell'),
                ]],
            ];

            $this->sendMessage($chatId, $message, 'Markdown', $keyboard);
        } catch (\Exception $e) {
            Log::error('handleRatesCommand error', ['error' => $e->getMessage()]);
            $this->sendMessage($chatId, "❌ Could not fetch rates. Please try again.");
        }
    }

    /**
     * Handle /balance command - show user's account balance
     */
    private function handleBalanceCommand($chatId)
    {
        $user = User::where('telegram_chat_id', $chatId)->first();

        if (!$user) {
            $this->sendMessage($chatId,
                "🔒 *Account Not Linked*\n\n" .
                "Send me your KayXchange email to link your account first.");
            return;
        }

        $appUrl = $this->telegramAppUrl();

        $message = "💰 *Your Balance*\n\n" .
                   "👤 {$user->name}\n" .
                   "💵 Available: ₦" . number_format($user->balance, 2) . "\n" .
                   "🔐 KYC: " . ($user->kyc_verified ? 'Verified ✅' : 'Pending ⏳') . "\n\n" .
                   "🌐 Dashboard: {$appUrl}/dashboard\n" .
                   "💳 Withdraw: {$appUrl}/withdraw/form\n\n" .
                   "_Last checked: " . now()->format('d M Y, H:i') . "_";

        $keyboard = [
            'inline_keyboard' => [[
                $this->linkButton('🌐 Open Dashboard', '/dashboard', 'cmd_start'),
                $this->linkButton('💳 Withdraw', '/withdraw/form', 'cmd_start'),
            ]],
        ];

        $this->sendMessage($chatId, $message, 'Markdown', $keyboard);
    }

    /**
     * Handle /sell command - full inline sell flow
     */
    private function handleSellCommand($chatId)
    {
        $user = User::where('telegram_chat_id', $chatId)->first();
        if (!$user) {
            $this->sendMessage($chatId,
                "🔒 *Account Not Linked*\n\nSend me your KayXchange email address to link your account before trading.");
            return;
        }

        // PIN gate — require PIN before starting trade
        if ($user->transaction_pin) {
            $this->clearState($chatId);
            $this->setState($chatId, 'sell_pin');
            $this->setData($chatId, ['user_id' => $user->id, 'pin_intent' => 'sell']);
            $this->sendMessage($chatId, "🔐 *Enter your 4-digit transaction PIN to continue.*\n\n_Type /cancel to abort._");
            return;
        }

        $this->clearState($chatId);
        $this->setState($chatId, 'sell_coin');
        $this->setData($chatId, ['user_id' => $user->id]);

        $rates = CryptoRate::whereIn('coin', ['BTC', 'ETH', 'USDT', 'SOL'])->get()->keyBy('coin');
        $msg = "💵 *Sell Cryptocurrency*\n\n*Live Sell Rates:*\n";
        foreach (['BTC', 'ETH', 'USDT', 'SOL'] as $c) {
            $r = isset($rates[$c]) ? number_format($rates[$c]->sell_rate, 2) : 'N/A';
            $msg .= "• *{$c}*: ₦{$r}/USD\n";
        }
        $msg .= "\nChoose the coin you want to sell:";

        $keyboard = ['inline_keyboard' => [
            [
                ['text' => '₿ BTC',  'callback_data' => 'sell_coin:BTC'],
                ['text' => 'Ξ ETH',  'callback_data' => 'sell_coin:ETH'],
                ['text' => '💲 USDT','callback_data' => 'sell_coin:USDT'],
                ['text' => '◎ SOL',  'callback_data' => 'sell_coin:SOL'],
            ],
            [['text' => '❌ Cancel', 'callback_data' => 'cancel']],
        ]];

        $this->sendMessage($chatId, $msg, 'Markdown', $keyboard);
    }

    private function handleSellCoinSelected(int $chatId, string $coin): void
    {
        $this->mergeData($chatId, ['coin' => $coin]);

        if ($coin === 'USDT') {
            $this->setState($chatId, 'sell_network');
            $keyboard = ['inline_keyboard' => [
                [
                    ['text' => 'TRC20 (TRON)', 'callback_data' => 'sell_network:TRC20'],
                    ['text' => 'ERC20 (ETH)',  'callback_data' => 'sell_network:ERC20'],
                    ['text' => 'BEP20 (BSC)',  'callback_data' => 'sell_network:BEP20'],
                ],
                [['text' => '❌ Cancel', 'callback_data' => 'cancel']],
            ]];
            $this->sendMessage($chatId, "🔗 *Choose USDT Network:*", 'Markdown', $keyboard);
            return;
        }

        $network = match($coin) { 'BTC' => 'BTC', 'ETH' => 'ERC20', 'SOL' => 'SOL', default => null };
        $this->mergeData($chatId, ['network' => $network]);
        $this->setState($chatId, 'sell_amount');

        $rate = CryptoRate::where('coin', $coin)->value('sell_rate') ?? 0;
        $this->mergeData($chatId, ['rate' => $rate]);
        $walletKey = $coin;
        $walletAddr = config("wallets.{$walletKey}", 'N/A');
        $this->sendMessage($chatId,
            "✅ *{$coin} selected*\n\n" .
            "📤 *Send your {$coin} to this address:*\n`{$walletAddr}`\n\n" .
            "💱 Sell rate: ₦" . number_format($rate, 2) . "/USD\n\n" .
            "💬 *Enter the USD amount you're selling* (e.g. `50`)\n" .
            "_Minimum: \$1_",
            'Markdown');
    }

    private function handleSellNetworkSelected(int $chatId, string $network): void
    {
        $data = $this->getData($chatId);
        $coin = $data['coin'] ?? 'USDT';
        $this->mergeData($chatId, ['network' => $network]);
        $this->setState($chatId, 'sell_amount');

        $rate = CryptoRate::where('coin', $coin)->value('sell_rate') ?? 0;
        $this->mergeData($chatId, ['rate' => $rate]);
        $walletKey = "USDT_{$network}";
        $walletAddr = config("wallets.{$walletKey}", config('wallets.USDT', 'N/A'));
        $this->sendMessage($chatId,
            "✅ *USDT ({$network}) selected*\n\n" .
            "📤 *Send your USDT to this address:*\n`{$walletAddr}`\n\n" .
            "💱 Sell rate: ₦" . number_format($rate, 2) . "/USD\n\n" .
            "💬 *Enter the USD amount you're selling* (e.g. `50`)\n" .
            "_Minimum: \$1_",
            'Markdown');
    }

    private function handleSellAmountInput(int $chatId, string $text): void
    {
        $amount = filter_var(trim($text), FILTER_VALIDATE_FLOAT);
        if ($amount === false || $amount < 1) {
            $this->sendMessage($chatId, "⚠️ Please enter a valid USD amount (numbers only, minimum \$1). Try again:");
            return;
        }

        $data    = $this->getData($chatId);
        $rate    = (float)($data['rate'] ?? 0);
        $naira   = round($amount * $rate, 2);
        $coin    = $data['coin'] ?? '?';
        $network = $data['network'] ?? '';

        $this->mergeData($chatId, ['usd_amount' => $amount, 'naira_amount' => $naira]);
        $this->setState($chatId, 'sell_proof');

        $netLabel = $network && $network !== $coin ? " ({$network})" : '';
        $this->sendMessage($chatId,
            "💰 *Trade Summary So Far:*\n\n" .
            "🪙 Coin: *{$coin}{$netLabel}*\n" .
            "💵 You sell: *\${$amount}*\n" .
            "💴 You receive: *₦" . number_format($naira, 2) . "*\n\n" .
            "📸 *Now upload your payment proof (screenshot/photo)*\n" .
            "_Send the photo as a Telegram photo (not a file)_",
            'Markdown');
    }

    private function handleSellProofUpload(int $chatId, string $fileId): void
    {
        $localPath = $this->downloadTelegramFile($fileId, 'payment_proofs');
        if (!$localPath) {
            $this->sendMessage($chatId, "❌ Could not save your proof photo. Please try again.");
            return;
        }

        $this->mergeData($chatId, ['proof' => $localPath]);
        $this->setState($chatId, 'sell_payout');

        $user = User::find($this->getData($chatId)['user_id'] ?? 0);
        $hasSavedBank = $user && !empty($user->bank_name) && $user->bank_name !== 'N/A';

        $rows = [];
        if ($hasSavedBank) {
            $rows[] = [['text' => "🏦 Default Bank ({$user->bank_name} — {$user->account_number})", 'callback_data' => 'sell_payout:default_bank']];
        }
        $rows[] = [['text' => '💰 Wallet Balance (add to app balance)', 'callback_data' => 'sell_payout:wallet_balance']];
        $rows[] = [['text' => '❌ Cancel', 'callback_data' => 'cancel']];

        $keyboard = ['inline_keyboard' => $rows];
        $this->sendMessage($chatId,
            "✅ *Proof received!*\n\nSelect your payout method:",
            'Markdown', $keyboard);
    }

    private function handleSellPayoutSelected(int $chatId, string $method): void
    {
        $this->mergeData($chatId, ['payout_method' => $method]);
        $this->setState($chatId, 'sell_confirm');

        $data    = $this->getData($chatId);
        $user    = User::find($data['user_id'] ?? 0);
        $coin    = $data['coin'] ?? '?';
        $network = $data['network'] ?? '';
        $usd     = $data['usd_amount'] ?? 0;
        $naira   = $data['naira_amount'] ?? 0;

        $payoutLabel = match($method) {
            'default_bank' => "🏦 Bank: {$user->bank_name} — {$user->account_number} ({$user->account_name})",
            'wallet_balance' => '💰 Add to Wallet Balance',
            default => $method,
        };

        $netLabel = ($network && $network !== $coin) ? " ({$network})" : '';
        $msg = "📋 *Confirm Your Sell Trade*\n\n" .
               "🪙 Coin: *{$coin}{$netLabel}*\n" .
               "💵 Amount: *\$" . number_format($usd, 2) . "*\n" .
               "💴 Payout: *₦" . number_format($naira, 2) . "*\n" .
               "💳 Method: {$payoutLabel}\n\n" .
               "⚠️ _Make sure you have already sent the crypto before confirming._";

        $keyboard = ['inline_keyboard' => [
            [
                ['text' => '✅ Confirm Trade', 'callback_data' => 'sell_confirm'],
                ['text' => '❌ Cancel',         'callback_data' => 'cancel'],
            ],
        ]];

        $this->sendMessage($chatId, $msg, 'Markdown', $keyboard);
    }

    private function handleSellConfirm(int $chatId): void
    {
        $data = $this->getData($chatId);
        $user = User::find($data['user_id'] ?? 0);

        if (!$user) {
            $this->sendMessage($chatId, "❌ Session expired. Please start again with /sell");
            $this->clearState($chatId);
            return;
        }

        $coin    = $data['coin']         ?? null;
        $network = $data['network']      ?? null;
        $usd     = $data['usd_amount']   ?? null;
        $naira   = $data['naira_amount'] ?? null;
        $proof   = $data['proof']        ?? null;
        $method  = $data['payout_method'] ?? null;

        if (!$coin || !$usd || !$proof || !$method) {
            $this->sendMessage($chatId, "❌ Missing trade data. Please start again with /sell");
            $this->clearState($chatId);
            return;
        }

        $walletKey  = ($coin === 'USDT' && $network) ? "USDT_{$network}" : $coin;
        $walletAddr = config("wallets.{$walletKey}", config("wallets.{$coin}", 'N/A'));

        DB::beginTransaction();
        try {
            $trade = SellTrade::create([
                'user_id'         => $user->id,
                'name'            => $user->name,
                'coin'            => $coin,
                'network'         => $network,
                'usd_amount'      => $usd,
                'naira_amount'    => $naira,
                'proof'           => $proof,
                'payment_method'  => $method,
                'status'          => 'pending',
                'transaction_ref' => 'SELL-TG-' . Str::upper(Str::random(8)),
                'wallet_address'  => $walletAddr,
                'bank_name'       => match($method) {
                    'default_bank'   => ($user->bank_name ?? 'N/A'),
                    'wallet_balance' => 'WALLET BALANCE',
                    default          => 'N/A',
                },
                'account_number'  => match($method) {
                    'default_bank'   => ($user->account_number ?? 'N/A'),
                    default          => 'N/A',
                },
                'account_name'    => match($method) {
                    'default_bank'   => ($user->account_name ?? 'N/A'),
                    'wallet_balance' => ($user->name ?? 'N/A'),
                    default          => 'N/A',
                },
            ]);

            DB::commit();
            $this->clearState($chatId);

            $this->sendMessage($chatId,
                "✅ *Sell Trade Submitted!*\n\n" .
                "🔖 Ref: `{$trade->transaction_ref}`\n" .
                "🪙 {$coin}: \$" . number_format($usd, 2) . "\n" .
                "💴 Payout: ₦" . number_format($naira, 2) . "\n" .
                "⏳ Status: *Pending Review*\n\n" .
                "We'll notify you once your trade is approved. Usually within 15–30 minutes.",
                'Markdown');

            // Notify admin
            try {
                app(\App\Services\AdminTradeAlertService::class)->sendTriggeredAlert('sell', [
                    'user_id'        => $user->id,
                    'reference'      => $trade->transaction_ref,
                    'user_name'      => $user->name,
                    'user_email'     => $user->email,
                    'coin'           => $coin,
                    'usd_amount'     => number_format($usd, 6),
                    'naira_amount'   => number_format($naira, 2),
                    'wallet_address' => $walletAddr,
                    'network'        => $network ?? 'N/A',
                    'status'         => 'pending',
                    'trade_id'       => $trade->id,
                    'via_telegram'   => true,
                ]);
            } catch (\Throwable $e) {
                Log::warning("Sell TG admin alert failed: {$e->getMessage()}");
            }

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("handleSellConfirm DB error: {$e->getMessage()}");
            $this->sendMessage($chatId, "❌ Trade submission failed. Please try again or use the website.");
        }
    }

    // ─────────────────────── BUY FLOW ───────────────────────

    /**
     * Handle /buy command - full inline buy flow
     */
    private function handleBuyCommand($chatId)
    {
        $user = User::where('telegram_chat_id', $chatId)->first();
        if (!$user) {
            $this->sendMessage($chatId,
                "🔒 *Account Not Linked*\n\nSend me your KayXchange email address to link your account before trading.");
            return;
        }

        // PIN gate — require PIN before starting trade
        if ($user->transaction_pin) {
            $this->clearState($chatId);
            $this->setState($chatId, 'buy_pin');
            $this->setData($chatId, ['user_id' => $user->id, 'pin_intent' => 'buy']);
            $this->sendMessage($chatId, "🔐 *Enter your 4-digit transaction PIN to continue.*\n\n_Type /cancel to abort._");
            return;
        }

        $this->clearState($chatId);
        $this->setState($chatId, 'buy_coin');
        $this->setData($chatId, ['user_id' => $user->id]);

        $rates = CryptoRate::whereIn('coin', ['BTC', 'ETH', 'USDT', 'SOL'])->get()->keyBy('coin');
        $msg = "💸 *Buy Cryptocurrency*\n\n*Live Buy Rates:*\n";
        foreach (['BTC', 'ETH', 'USDT', 'SOL'] as $c) {
            $r = isset($rates[$c]) ? number_format($rates[$c]->buy_rate, 2) : 'N/A';
            $msg .= "• *{$c}*: ₦{$r}/USD\n";
        }
        $msg .= "\nChoose the coin you want to buy:";

        $keyboard = ['inline_keyboard' => [
            [
                ['text' => '₿ BTC',  'callback_data' => 'buy_coin:BTC'],
                ['text' => 'Ξ ETH',  'callback_data' => 'buy_coin:ETH'],
                ['text' => '💲 USDT','callback_data' => 'buy_coin:USDT'],
                ['text' => '◎ SOL',  'callback_data' => 'buy_coin:SOL'],
            ],
            [['text' => '❌ Cancel', 'callback_data' => 'cancel']],
        ]];

        $this->sendMessage($chatId, $msg, 'Markdown', $keyboard);
    }

    private function handleBuyCoinSelected(int $chatId, string $coin): void
    {
        $this->mergeData($chatId, ['coin' => $coin]);

        if ($coin === 'USDT') {
            $this->setState($chatId, 'buy_network');
            $keyboard = ['inline_keyboard' => [
                [
                    ['text' => 'TRC20 (TRON)', 'callback_data' => 'buy_network:TRC20'],
                    ['text' => 'ERC20 (ETH)',  'callback_data' => 'buy_network:ERC20'],
                    ['text' => 'BEP20 (BSC)',  'callback_data' => 'buy_network:BEP20'],
                ],
                [['text' => '❌ Cancel', 'callback_data' => 'cancel']],
            ]];
            $this->sendMessage($chatId, "🔗 *Choose USDT Network:*", 'Markdown', $keyboard);
            return;
        }

        $network = match($coin) { 'BTC' => 'BTC', 'ETH' => 'ERC20', 'SOL' => 'SOL', default => null };
        $this->mergeData($chatId, ['network' => $network]);
        $this->askBuyAmount($chatId, $coin, $network);
    }

    private function handleBuyNetworkSelected(int $chatId, string $network): void
    {
        $this->mergeData($chatId, ['network' => $network]);
        $data = $this->getData($chatId);
        $this->askBuyAmount($chatId, $data['coin'] ?? 'USDT', $network);
    }

    private function askBuyAmount(int $chatId, string $coin, ?string $network): void
    {
        $rate = CryptoRate::where('coin', $coin)->value('buy_rate') ?? 0;
        $this->mergeData($chatId, ['rate' => $rate]);
        $this->setState($chatId, 'buy_amount');
        $netLabel = ($network && $network !== $coin) ? " ({$network})" : '';
        $this->sendMessage($chatId,
            "✅ *{$coin}{$netLabel} selected*\n\n" .
            "💱 Buy rate: ₦" . number_format($rate, 2) . "/USD\n\n" .
            "💬 *Enter the amount in NGN you want to spend* (e.g. `50000`)\n" .
            "_Minimum: ₦1,000_",
            'Markdown');
    }

    private function handleBuyAmountInput(int $chatId, string $text): void
    {
        $amount = filter_var(str_replace(',', '', trim($text)), FILTER_VALIDATE_FLOAT);
        if ($amount === false || $amount < 1000) {
            $this->sendMessage($chatId, "⚠️ Please enter a valid NGN amount (minimum ₦1,000). Try again:");
            return;
        }

        $data  = $this->getData($chatId);
        $rate  = (float)($data['rate'] ?? 0);
        $usd   = $rate > 0 ? round($amount / $rate, 6) : 0;

        $this->mergeData($chatId, ['naira_amount' => $amount, 'usd_amount' => $usd]);
        $this->setState($chatId, 'buy_wallet');

        $coin    = $data['coin'] ?? '?';
        $network = $data['network'] ?? '';
        $netLabel = ($network && $network !== $coin) ? " ({$network})" : '';

        $this->sendMessage($chatId,
            "💰 *Order Summary:*\n\n" .
            "🪙 Coin: *{$coin}{$netLabel}*\n" .
            "💴 You pay: *₦" . number_format($amount, 2) . "*\n" .
            "💵 You receive: *≈{$usd} {$coin}*\n\n" .
            "📬 *Enter your {$coin} wallet address* (where you want to receive the crypto):",
            'Markdown');
    }

    private function handleBuyWalletInput(int $chatId, string $wallet): void
    {
        $wallet = trim($wallet);
        if (strlen($wallet) < 10) {
            $this->sendMessage($chatId, "⚠️ That doesn't look like a valid wallet address. Please try again:");
            return;
        }

        $this->mergeData($chatId, ['recipient_wallet' => $wallet]);
        $this->setState($chatId, 'buy_proof');

        $data  = $this->getData($chatId);
        $coin  = $data['coin'] ?? '?';
        $naira = $data['naira_amount'] ?? 0;

        // Show company account for payment
        $companyAccount = $this->getCompanyBankDetails();

        $msg = "📋 *Payment Instructions*\n\n" .
               "Transfer *₦" . number_format($naira, 2) . "* to:\n\n" .
               "🏦 *Bank:* {$companyAccount['bank']}\n" .
               "💳 *Account:* `{$companyAccount['number']}`\n" .
               "👤 *Name:* {$companyAccount['name']}\n\n" .
               "📸 After payment, *upload your payment screenshot/receipt as a photo*:";

        $this->sendMessage($chatId, $msg, 'Markdown');
    }

    private function handleBuyProofUpload(int $chatId, string $fileId): void
    {
        $localPath = $this->downloadTelegramFile($fileId, 'payment_proofs');
        if (!$localPath) {
            $this->sendMessage($chatId, "❌ Could not save your proof photo. Please try again.");
            return;
        }

        $this->mergeData($chatId, ['proof' => $localPath]);
        $this->setState($chatId, 'buy_confirm');

        $data    = $this->getData($chatId);
        $coin    = $data['coin'] ?? '?';
        $network = $data['network'] ?? '';
        $naira   = $data['naira_amount'] ?? 0;
        $usd     = $data['usd_amount'] ?? 0;
        $wallet  = $data['recipient_wallet'] ?? '?';

        $netLabel = ($network && $network !== $coin) ? " ({$network})" : '';
        $msg = "📋 *Confirm Your Buy Order*\n\n" .
               "🪙 Coin: *{$coin}{$netLabel}*\n" .
               "💴 You pay: *₦" . number_format($naira, 2) . "*\n" .
               "💵 You receive: *≈{$usd} {$coin}*\n" .
               "📬 Your wallet: `{$wallet}`\n\n" .
               "💳 Payment method: Bank Transfer";

        $keyboard = ['inline_keyboard' => [
            [
                ['text' => '✅ Confirm Order', 'callback_data' => 'buy_confirm'],
                ['text' => '❌ Cancel',         'callback_data' => 'cancel'],
            ],
        ]];

        $this->sendMessage($chatId, $msg, 'Markdown', $keyboard);
    }

    private function handleBuyConfirm(int $chatId): void
    {
        $data = $this->getData($chatId);
        $user = User::find($data['user_id'] ?? 0);

        if (!$user) {
            $this->sendMessage($chatId, "❌ Session expired. Please start again with /buy");
            $this->clearState($chatId);
            return;
        }

        $coin    = $data['coin']             ?? null;
        $network = $data['network']          ?? null;
        $usd     = $data['usd_amount']       ?? null;
        $naira   = $data['naira_amount']     ?? null;
        $proof   = $data['proof']            ?? null;
        $wallet  = $data['recipient_wallet'] ?? null;

        if (!$coin || !$usd || !$proof || !$wallet) {
            $this->sendMessage($chatId, "❌ Missing trade data. Please start again with /buy");
            $this->clearState($chatId);
            return;
        }

        DB::beginTransaction();
        try {
            $trade = BuyTrade::create([
                'user_id'          => $user->id,
                'name'             => $user->name,
                'coin'             => $coin,
                'network'          => $network,
                'usd_amount'       => $usd,
                'naira_amount'     => $naira,
                'wallet_address'   => $wallet,
                'payment_proof'    => $proof,
                'payment_method'   => 'Bank Transfer',
                'status'           => 'pending',
                'transaction_ref'  => 'BUY-TG-' . Str::upper(Str::random(8)),
                'transaction_type' => 'buy',
                'ip_address'       => '0.0.0.0',
            ]);

            DB::commit();
            $this->clearState($chatId);

            $this->sendMessage($chatId,
                "✅ *Buy Order Submitted!*\n\n" .
                "🔖 Ref: `{$trade->transaction_ref}`\n" .
                "🪙 {$coin}: ≈{$usd}\n" .
                "💴 You paid: ₦" . number_format($naira, 2) . "\n" .
                "⏳ Status: *Pending Review*\n\n" .
                "We'll send the crypto to your wallet once your payment is confirmed. Usually within 15–30 minutes.",
                'Markdown');

            try {
                app(\App\Services\AdminTradeAlertService::class)->sendTriggeredAlert('buy', [
                    'user_id'        => $user->id,
                    'reference'      => $trade->transaction_ref,
                    'user_name'      => $user->name,
                    'user_email'     => $user->email,
                    'coin'           => $coin,
                    'usd_amount'     => number_format($usd, 6),
                    'naira_amount'   => number_format($naira, 2),
                    'wallet_address' => $wallet,
                    'network'        => $network ?? 'N/A',
                    'status'         => 'pending',
                    'trade_id'       => $trade->id,
                    'via_telegram'   => true,
                ]);
            } catch (\Throwable $e) {
                Log::warning("Buy TG admin alert failed: {$e->getMessage()}");
            }

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("handleBuyConfirm DB error: {$e->getMessage()}");
            $this->sendMessage($chatId, "❌ Order submission failed. Please try again or use the website.");
        }
    }

    /**
     * Get company bank details for buy payments.
     * Reads from the first CompanyAccount record, or falls back to env.
     */
    private function getCompanyBankDetails(): array
    {
        try {
            $ca = \App\Models\CompanyAccount::first();
            if ($ca) {
                return [
                    'bank'   => $ca->bank_name ?? 'KayXchange Bank',
                    'number' => $ca->account_number ?? 'N/A',
                    'name'   => $ca->account_name ?? 'KayXchange',
                ];
            }
        } catch (\Throwable $e) { /* no table – fall through */ }

        return [
            'bank'   => env('COMPANY_BANK_NAME', 'See website'),
            'number' => env('COMPANY_ACCOUNT_NUMBER', 'See website'),
            'name'   => env('COMPANY_ACCOUNT_NAME', 'KayXchange'),
        ];
    }

    /**
     * Download a Telegram file and save it to local storage.
     * Returns the storage-relative path (e.g. payment_proofs/abc.jpg) or null on failure.
     */
    private function downloadTelegramFile(string $fileId, string $folder): ?string
    {
        try {
            $res = Http::get("{$this->apiUrl}/getFile", ['file_id' => $fileId]);
            if (!$res->successful() || empty($res->json('result.file_path'))) {
                return null;
            }
            $filePath = $res->json('result.file_path');
            $ext      = pathinfo($filePath, PATHINFO_EXTENSION) ?: 'jpg';
            $content  = Http::get("https://api.telegram.org/file/bot{$this->botToken}/{$filePath}")->body();
            if (empty($content)) return null;

            $localPath = "{$folder}/" . Str::random(40) . ".{$ext}";
            Storage::disk('public')->put($localPath, $content);
            return $localPath;
        } catch (\Throwable $e) {
            Log::error('downloadTelegramFile failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Handle /trades command - show user's last 5 trades
     */
    private function handleTradesCommand($chatId)
    {
        $user = User::where('telegram_chat_id', $chatId)->first();

        if (!$user) {
            $this->sendMessage($chatId,
                "🔒 *Account Not Linked*\n\n" .
                "Send me your KayXchange email to link your account first.");
            return;
        }

        $buys  = \App\Models\BuyTrade::where('user_id', $user->id)->latest()->take(5)->get();
        $sells = \App\Models\SellTrade::where('user_id', $user->id)->latest()->take(5)->get();

        // Merge and sort by created_at, take latest 5
        $trades = $buys->map(fn($t) => ['type' => 'Buy', 'coin' => $t->coin, 'amount' => $t->naira_amount, 'status' => $t->status, 'date' => $t->created_at])
            ->merge($sells->map(fn($t) => ['type' => 'Sell', 'coin' => $t->coin, 'amount' => $t->naira_amount, 'status' => $t->status, 'date' => $t->created_at]))
            ->sortByDesc('date')
            ->take(5);

        if ($trades->isEmpty()) {
            $this->sendMessage($chatId, "📭 You have no trades yet.\n\nUse /buy or /sell to get started!");
            return;
        }

        $message = "📋 *Your Recent Trades*\n\n";
        foreach ($trades as $trade) {
            $statusEmoji = match(strtolower($trade['status'])) {
                'completed', 'approved', 'successful' => '✅',
                'pending' => '⏳',
                'cancelled', 'rejected' => '❌',
                default => '🔄',
            };
            $amount = number_format($trade['amount'], 2);
            $date   = $trade['date']->format('d M, H:i');
            $message .= "{$statusEmoji} *{$trade['type']} {$trade['coin']}* — ₦{$amount}\n";
            $message .= "   Status: {$trade['status']} | {$date}\n\n";
        }

        $appUrl = $this->telegramAppUrl();
        $message .= "📜 Full History: {$appUrl}/dashboard";

        $keyboard = [
            'inline_keyboard' => [[
                $this->linkButton('📜 Full History', '/dashboard', 'cmd_start'),
            ]],
        ];

        $this->sendMessage($chatId, $message, 'Markdown', $keyboard);
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
     * Handle cancel command — abort any active trade flow
     */
    private function handleCancelCommand(int $chatId): void
    {
        $had = $this->getState($chatId) !== null;
        $this->clearState($chatId);
        if ($had) {
            $this->sendMessage($chatId, "❌ *Trade cancelled.*\n\nUse /sell, /buy, or /help anytime.", 'Markdown');
        } else {
            $this->sendMessage($chatId, "Nothing to cancel. Use /help to see available commands.");
        }
    }

    // ─────────────────────── ADMIN APPROVE / REJECT ──────────────────────────

    private function isAdminChatId(int $chatId): bool
    {
        return User::where('telegram_chat_id', $chatId)->where('is_admin', true)->exists();
    }

    private function editMessage(int $chatId, int $messageId, string $text): void
    {
        try {
            Http::post("{$this->apiUrl}/editMessageText", [
                'chat_id'    => $chatId,
                'message_id' => $messageId,
                'text'       => $text,
                'parse_mode' => 'Markdown',
                'disable_web_page_preview' => true,
            ]);
        } catch (\Throwable $e) {
            Log::warning('editMessage failed: ' . $e->getMessage());
        }
    }

    public function handleAdminApproveSell(int $chatId, int $tradeId, ?int $messageId): void
    {
        if (!$this->isAdminChatId($chatId)) {
            $this->sendMessage($chatId, "⛔ You are not authorised to perform this action.");
            return;
        }

        $trade = SellTrade::find($tradeId);
        if (!$trade) {
            $this->sendMessage($chatId, "❌ Sell trade #{$tradeId} not found.");
            return;
        }

        if ($trade->status !== 'pending') {
            $this->sendMessage($chatId, "⚠️ Trade #{$tradeId} is already *{$trade->status}*.", 'Markdown');
            return;
        }

        DB::beginTransaction();
        try {
            $oldStatus = $trade->status;
            $trade->status = 'completed';
            $trade->save();

            // Credit wallet balance if payout method is wallet_balance
            if ($trade->payment_method === 'wallet_balance') {
                $user = User::find($trade->user_id);
                if ($user) {
                    $user->increment('balance', $trade->naira_amount);
                }
            }

            DB::commit();

            if ($messageId) {
                $this->editMessage($chatId, $messageId,
                    "✅ *APPROVED* — Sell Trade #{$tradeId}\nRef: {$trade->transaction_ref}\n" .
                    "User: {$trade->name} | ₦" . number_format($trade->naira_amount, 2));
            }

            // Notify the user
            $tradeUser = User::find($trade->user_id);
            if ($tradeUser && $tradeUser->telegram_chat_id) {
                $this->sendMessage((int)$tradeUser->telegram_chat_id,
                    "🎉 *Your Sell Trade is Approved!*\n\n" .
                    "🔖 Ref: `{$trade->transaction_ref}`\n" .
                    "💴 Amount: ₦" . number_format($trade->naira_amount, 2) . "\n" .
                    "💳 Method: {$trade->payment_method}\n\n" .
                    ($trade->payment_method === 'wallet_balance'
                        ? "💰 ₦" . number_format($trade->naira_amount, 2) . " has been credited to your wallet balance."
                        : "💳 Payment is being processed to your bank account."),
                    'Markdown');
            }

            // Alert service status change
            try {
                app(AdminTradeAlertService::class)->sendStatusChangeAlert('sell', [
                    'reference'  => $trade->transaction_ref,
                    'user_name'  => $trade->name,
                    'old_status' => $oldStatus,
                    'new_status' => 'completed',
                ]);
            } catch (\Throwable $e) {}

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("handleAdminApproveSell error: {$e->getMessage()}");
            $this->sendMessage($chatId, "❌ Failed to approve trade: {$e->getMessage()}");
        }
    }

    public function handleAdminRejectSell(int $chatId, int $tradeId, ?int $messageId): void
    {
        if (!$this->isAdminChatId($chatId)) {
            $this->sendMessage($chatId, "⛔ You are not authorised to perform this action.");
            return;
        }

        $trade = SellTrade::find($tradeId);
        if (!$trade) {
            $this->sendMessage($chatId, "❌ Sell trade #{$tradeId} not found.");
            return;
        }

        if ($trade->status !== 'pending') {
            $this->sendMessage($chatId, "⚠️ Trade #{$tradeId} is already *{$trade->status}*.", 'Markdown');
            return;
        }

        $oldStatus = $trade->status;
        $trade->status = 'rejected';
        $trade->save();

        if ($messageId) {
            $this->editMessage($chatId, $messageId,
                "❌ *REJECTED* — Sell Trade #{$tradeId}\nRef: {$trade->transaction_ref}\n" .
                "User: {$trade->name} | ₦" . number_format($trade->naira_amount, 2));
        }

        $tradeUser = User::find($trade->user_id);
        if ($tradeUser && $tradeUser->telegram_chat_id) {
            $this->sendMessage((int)$tradeUser->telegram_chat_id,
                "❌ *Your Sell Trade was Rejected*\n\n" .
                "🔖 Ref: `{$trade->transaction_ref}`\n" .
                "💴 Amount: ₦" . number_format($trade->naira_amount, 2) . "\n\n" .
                "Please contact support for more details.",
                'Markdown');
        }

        try {
            app(AdminTradeAlertService::class)->sendStatusChangeAlert('sell', [
                'reference'  => $trade->transaction_ref,
                'user_name'  => $trade->name,
                'old_status' => $oldStatus,
                'new_status' => 'rejected',
            ]);
        } catch (\Throwable $e) {}
    }

    public function handleAdminApproveBuy(int $chatId, int $tradeId, ?int $messageId): void
    {
        if (!$this->isAdminChatId($chatId)) {
            $this->sendMessage($chatId, "⛔ You are not authorised to perform this action.");
            return;
        }

        $trade = BuyTrade::find($tradeId);
        if (!$trade) {
            $this->sendMessage($chatId, "❌ Buy trade #{$tradeId} not found.");
            return;
        }

        if ($trade->status !== 'pending') {
            $this->sendMessage($chatId, "⚠️ Trade #{$tradeId} is already *{$trade->status}*.", 'Markdown');
            return;
        }

        $oldStatus = $trade->status;
        $trade->status = 'completed';
        $trade->save();

        if ($messageId) {
            $this->editMessage($chatId, $messageId,
                "✅ *APPROVED* — Buy Trade #{$tradeId}\nRef: {$trade->transaction_ref}\n" .
                "User: {$trade->name} | ₦" . number_format($trade->naira_amount, 2));
        }

        $tradeUser = User::find($trade->user_id);
        if ($tradeUser && $tradeUser->telegram_chat_id) {
            $this->sendMessage((int)$tradeUser->telegram_chat_id,
                "🎉 *Your Buy Order is Approved!*\n\n" .
                "🔖 Ref: `{$trade->transaction_ref}`\n" .
                "🪙 {$trade->coin}: ≈{$trade->usd_amount}\n" .
                "📬 Sending to: `{$trade->wallet_address}`\n\n" .
                "Your crypto will arrive within a few minutes.",
                'Markdown');
        }

        try {
            app(AdminTradeAlertService::class)->sendStatusChangeAlert('buy', [
                'reference'  => $trade->transaction_ref,
                'user_name'  => $trade->name,
                'old_status' => $oldStatus,
                'new_status' => 'completed',
            ]);
        } catch (\Throwable $e) {}
    }

    public function handleAdminRejectBuy(int $chatId, int $tradeId, ?int $messageId): void
    {
        if (!$this->isAdminChatId($chatId)) {
            $this->sendMessage($chatId, "⛔ You are not authorised to perform this action.");
            return;
        }

        $trade = BuyTrade::find($tradeId);
        if (!$trade) {
            $this->sendMessage($chatId, "❌ Buy trade #{$tradeId} not found.");
            return;
        }

        if ($trade->status !== 'pending') {
            $this->sendMessage($chatId, "⚠️ Trade #{$tradeId} is already *{$trade->status}*.", 'Markdown');
            return;
        }

        $oldStatus = $trade->status;
        $trade->status = 'rejected';
        $trade->save();

        if ($messageId) {
            $this->editMessage($chatId, $messageId,
                "❌ *REJECTED* — Buy Trade #{$tradeId}\nRef: {$trade->transaction_ref}\n" .
                "User: {$trade->name} | ₦" . number_format($trade->naira_amount, 2));
        }

        $tradeUser = User::find($trade->user_id);
        if ($tradeUser && $tradeUser->telegram_chat_id) {
            $this->sendMessage((int)$tradeUser->telegram_chat_id,
                "❌ *Your Buy Order was Rejected*\n\n" .
                "🔖 Ref: `{$trade->transaction_ref}`\n" .
                "💴 Amount: ₦" . number_format($trade->naira_amount, 2) . "\n\n" .
                "Your payment was not confirmed. Please contact support.",
                'Markdown');
        }

        try {
            app(AdminTradeAlertService::class)->sendStatusChangeAlert('buy', [
                'reference'  => $trade->transaction_ref,
                'user_name'  => $trade->name,
                'old_status' => $oldStatus,
                'new_status' => 'rejected',
            ]);
        } catch (\Throwable $e) {}
    }

    public function handleAdminApproveKyc(int $chatId, int $kycId, ?int $messageId): void
    {
        if (!$this->isAdminChatId($chatId)) {
            $this->sendMessage($chatId, "⛔ You are not authorised to perform this action.");
            return;
        }

        $kyc = Kyc::with('user')->find($kycId);
        if (!$kyc) {
            $this->sendMessage($chatId, "❌ KYC submission #{$kycId} not found.");
            return;
        }

        if ($kyc->status !== 'pending') {
            $this->sendMessage($chatId, "⚠️ KYC #{$kycId} is already *{$kyc->status}*.", 'Markdown');
            return;
        }

        $kyc->status = 'approved';
        $kyc->save();

        if ($kyc->user) {
            $kyc->user->kyc_verified = 1;
            $kyc->user->save();
        }

        if ($messageId) {
            $name = $kyc->user->name ?? 'Unknown User';
            $this->editMessage($chatId, $messageId,
                "✅ *KYC APPROVED* — Submission #{$kycId}\n" .
                "User: {$name}\n" .
                "Status: approved");
        }

        if ($kyc->user && $kyc->user->telegram_chat_id) {
            $this->sendMessage((int)$kyc->user->telegram_chat_id,
                "✅ *KYC Approved*\n\nYour verification has been approved. You can now enjoy full account access.",
                'Markdown');
        }
    }

    public function handleAdminRejectKyc(int $chatId, int $kycId, ?int $messageId): void
    {
        if (!$this->isAdminChatId($chatId)) {
            $this->sendMessage($chatId, "⛔ You are not authorised to perform this action.");
            return;
        }

        $kyc = Kyc::with('user')->find($kycId);
        if (!$kyc) {
            $this->sendMessage($chatId, "❌ KYC submission #{$kycId} not found.");
            return;
        }

        if ($kyc->status !== 'pending') {
            $this->sendMessage($chatId, "⚠️ KYC #{$kycId} is already *{$kyc->status}*.", 'Markdown');
            return;
        }

        $kyc->status = 'rejected';
        $kyc->save();

        if ($kyc->user) {
            $kyc->user->kyc_verified = 0;
            $kyc->user->save();
        }

        if ($messageId) {
            $name = $kyc->user->name ?? 'Unknown User';
            $this->editMessage($chatId, $messageId,
                "❌ *KYC REJECTED* — Submission #{$kycId}\n" .
                "User: {$name}\n" .
                "Status: rejected");
        }

        if ($kyc->user && $kyc->user->telegram_chat_id) {
            $this->sendMessage((int)$kyc->user->telegram_chat_id,
                "❌ *KYC Rejected*\n\nYour verification could not be approved. Please re-upload clear documents from your dashboard.",
                'Markdown');
        }
    }

    public function handleAdminApproveWithdrawal(int $chatId, int $withdrawalId, ?int $messageId): void
    {
        if (!$this->isAdminChatId($chatId)) {
            $this->sendMessage($chatId, "⛔ You are not authorised to perform this action.");
            return;
        }

        $withdrawal = \App\Models\Withdrawal::with('user')->find($withdrawalId);
        if (!$withdrawal) {
            $this->sendMessage($chatId, "❌ Withdrawal #{$withdrawalId} not found.");
            return;
        }

        if ($withdrawal->status !== 'pending') {
            $this->sendMessage($chatId, "⚠️ Withdrawal #{$withdrawalId} is already *{$withdrawal->status}*.", 'Markdown');
            return;
        }

        $user = $withdrawal->user;
        if (!$user) {
            $this->sendMessage($chatId, "❌ User for withdrawal #{$withdrawalId} not found.");
            return;
        }

        DB::beginTransaction();
        try {
            if ($user->balance < $withdrawal->amount) {
                DB::rollBack();
                $this->sendMessage($chatId, "❌ Insufficient balance — cannot approve withdrawal #{$withdrawalId}.");
                return;
            }

            $user->balance -= $withdrawal->amount;
            $user->save();

            $withdrawal->status       = 'approved';
            $withdrawal->processed_at = now();
            $withdrawal->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("handleAdminApproveWithdrawal error: {$e->getMessage()}");
            $this->sendMessage($chatId, "❌ Failed to approve withdrawal: {$e->getMessage()}");
            return;
        }

        if ($messageId) {
            $bd = is_array($withdrawal->bank_account)
                ? $withdrawal->bank_account
                : (json_decode($withdrawal->bank_account, true) ?? []);
            $this->editMessage($chatId, $messageId,
                "✅ *APPROVED* — Withdrawal #{$withdrawalId}\n" .
                "Ref: {$withdrawal->reference}\n" .
                "User: {$user->name} | ₦" . number_format($withdrawal->amount, 2) . "\n" .
                "Bank: " . ($bd['bank_name'] ?? 'N/A') . " · " . ($bd['account_number'] ?? 'N/A'));
        }

        if ($user->telegram_chat_id) {
            $this->sendMessage((int) $user->telegram_chat_id,
                "✅ *Withdrawal Approved!*\n\n" .
                "🔖 Ref: `{$withdrawal->reference}`\n" .
                "💴 Amount: ₦" . number_format($withdrawal->amount, 2) . "\n\n" .
                "Your payment is being processed to your bank account.",
                'Markdown');
        }

        try {
            app(AdminTradeAlertService::class)->sendStatusChangeAlert('withdrawal', [
                'reference'  => $withdrawal->reference,
                'user_name'  => $user->name,
                'old_status' => 'pending',
                'new_status' => 'approved',
            ]);
        } catch (\Throwable $e) {}
    }

    public function handleAdminRejectWithdrawal(int $chatId, int $withdrawalId, ?int $messageId): void
    {
        if (!$this->isAdminChatId($chatId)) {
            $this->sendMessage($chatId, "⛔ You are not authorised to perform this action.");
            return;
        }

        $withdrawal = \App\Models\Withdrawal::with('user')->find($withdrawalId);
        if (!$withdrawal) {
            $this->sendMessage($chatId, "❌ Withdrawal #{$withdrawalId} not found.");
            return;
        }

        if ($withdrawal->status !== 'pending') {
            $this->sendMessage($chatId, "⚠️ Withdrawal #{$withdrawalId} is already *{$withdrawal->status}*.", 'Markdown');
            return;
        }

        $user          = $withdrawal->user;
        $oldStatus     = $withdrawal->status;
        $withdrawal->status       = 'cancelled';
        $withdrawal->processed_at = now();
        $withdrawal->save();

        if ($messageId) {
            $this->editMessage($chatId, $messageId,
                "❌ *REJECTED* — Withdrawal #{$withdrawalId}\n" .
                "Ref: {$withdrawal->reference}\n" .
                "User: " . ($user->name ?? 'N/A') . " | ₦" . number_format($withdrawal->amount, 2));
        }

        if ($user && $user->telegram_chat_id) {
            $this->sendMessage((int) $user->telegram_chat_id,
                "❌ *Withdrawal Rejected*\n\n" .
                "🔖 Ref: `{$withdrawal->reference}`\n" .
                "💴 Amount: ₦" . number_format($withdrawal->amount, 2) . "\n\n" .
                "Please contact support for more details.",
                'Markdown');
        }

        try {
            app(AdminTradeAlertService::class)->sendStatusChangeAlert('withdrawal', [
                'reference'  => $withdrawal->reference,
                'user_name'  => $user->name ?? 'N/A',
                'old_status' => $oldStatus,
                'new_status' => 'cancelled',
            ]);
        } catch (\Throwable $e) {}
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