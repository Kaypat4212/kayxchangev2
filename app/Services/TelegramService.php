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
use App\Models\CryptoDeposit;
use App\Models\GiftCardRate;
// use App\Models\CryptoRate;
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

    private function deleteSensitiveMessage(int $chatId, int $messageId): void
    {
        try {
            Http::timeout(4)->post("{$this->apiUrl}/deleteMessage", [
                'chat_id' => $chatId,
                'message_id' => $messageId,
            ]);
        } catch (\Throwable $e) {
            Log::info('KayX bot could not delete a sensitive Telegram message', ['chat_id' => $chatId]);
        }
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
        // Prefer TELEGRAM_APP_URL if set, otherwise fall back to APP_URL
        $appUrl = env('TELEGRAM_APP_URL', env('APP_URL', ''));
        return str_starts_with($appUrl, 'https://') &&
               !str_contains($appUrl, 'localhost') &&
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
     * Send a photo file to a Telegram chat.
     *
     * @param  int|string  $chatId
     * @param  string      $photoPath  Absolute filesystem path to the image
     * @param  string      $caption    Optional caption (Markdown supported)
     * @param  string      $parseMode
     * @return bool
     */
    private function sendPhoto($chatId, string $photoPath, string $caption = '', string $parseMode = 'Markdown'): bool
    {
        try {
            if (!file_exists($photoPath)) {
                Log::warning('sendPhoto: file not found', ['path' => $photoPath]);
                return false;
            }

            $response = Http::attach(
                'photo',
                file_get_contents($photoPath),
                basename($photoPath)
            )->post("{$this->apiUrl}/sendPhoto", array_filter([
                'chat_id'    => $chatId,
                'caption'    => $caption ?: null,
                'parse_mode' => $caption ? $parseMode : null,
            ]));

            $result = $response->json();
            if ($response->successful() && ($result['ok'] ?? false)) {
                return true;
            }

            Log::warning('Failed to send Telegram photo', [
                'chat_id' => $chatId,
                'error'   => $result['description'] ?? 'Unknown',
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::error('Exception sending Telegram photo', [
                'chat_id' => $chatId,
                'error'   => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Download a QR code image for the given wallet address and return its temp path.
     * Returns null on failure. Caller is responsible for deleting the temp file.
     */
    private function getBarcodePathForWallet(string $walletAddress): ?string
    {
        if (empty($walletAddress) || $walletAddress === 'N/A') {
            return null;
        }

        try {
            $url  = 'https://api.qrserver.com/v1/create-qr-code/?size=400x400&margin=10&data=' . urlencode($walletAddress);
            $data = file_get_contents($url, false, stream_context_create([
                'http' => ['timeout' => 8],
                'ssl'  => ['verify_peer' => false],
            ]));

            if (!$data) {
                return null;
            }

            $tmpPath = sys_get_temp_dir() . '/kx_qr_' . md5($walletAddress) . '.png';
            file_put_contents($tmpPath, $data);
            return $tmpPath;
        } catch (\Throwable $e) {
            Log::warning('Failed to generate QR for wallet: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * @deprecated Use getBarcodePathForWallet() with the actual wallet address instead.
     * Resolve the public barcode image path for a given coin/network.
     * Returns null when no barcode file is found.
     */
    private function getBarcodePathForCoin(string $coin, ?string $network): ?string
    {
        $key = ($coin === 'USDT' && $network) ? "USDT_{$network}" : $coin;
        $walletAddress = config("wallets.{$key}", config("wallets.{$coin}", ''));
        return $this->getBarcodePathForWallet($walletAddress);
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
                'disable_web_page_preview' => true,
            ];
            if ($parseMode) $payload['parse_mode'] = $parseMode;

            if ($keyboard !== null) {
                $payload['reply_markup'] = json_encode($keyboard);
            }

            $response = Http::post("{$this->apiUrl}/sendMessage", $payload);

            $result = $response->json();

            // Dynamic user data can contain Markdown control characters. If that
            // makes Telegram reject entity parsing, retry once as plain text so
            // transaction and status alerts are still delivered.
            $description = (string) ($result['description'] ?? '');
            if (!$response->successful()
                && $parseMode
                && stripos($description, "can't parse entities") !== false) {
                unset($payload['parse_mode']);
                $response = Http::post("{$this->apiUrl}/sendMessage", $payload);
                $result = $response->json();
                if ($response->successful() && ($result['ok'] ?? false)) {
                    Log::warning('Telegram message delivered as plain text after Markdown parsing failed.', ['chat_id' => $chatId]);
                    return $result;
                }
            }

            if ($response->successful() && ($result['ok'] ?? false)) {
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

        if (!str_starts_with($text, '/')) {
            $lower = mb_strtolower($text);
            if (preg_match('/\b(gift[\s-]?cards?|itunes cards?)\b.*\b(status|track|delivered|ready|check|where)\b|\b(status|track|delivered|ready|check)\b.*\b(gift[\s-]?cards?|itunes cards?)\b/i', $lower)) return 'giftcard_status';
            if (preg_match('/\b(abeg|please|show|check|see|view|wetin|how much)\b.*\b(balance|wallet|money)\b|\b(balance|wallet)\b.*\b(mine|my|abeg)\b/i', $lower)) return 'balance';
            if (preg_match('/\b(history|transaction|transactions|recent trades|my trades|account activity|show all tx)\b/i', $lower)) return 'trades';
            if (preg_match('/\b(wetin be|abeg show|show|check|current|live|today)\b.*\b(rate|rates|price|prices)\b|\b(rate|rates|price|prices)\b.*\b(una|today|abeg)\b/i', $lower)) return 'rates';
            if (preg_match('/\b(i wan|i want|make i|abeg)\s+(?:to\s+)?buy\b|\bbuy\s+(crypto|btc|eth|usdt|usdc|sol|bnb|bitcoin|ethereum)\b/i', $lower)) return 'buy';
            if (preg_match('/\b(i wan|i want|make i|abeg)\s+(?:to\s+)?sell\b|\bsell\s+(crypto|btc|eth|usdt|usdc|sol|bnb|bitcoin|ethereum)\b/i', $lower)) return 'sell';
        }

        $known = ['start', 'register', 'rates', 'buy', 'sell', 'deposit', 'withdraw', 'balance', 'trades', 'history', 'transactions', 'verify', 'status', 'help', 'cancel', 'ai', 'upload', 'features', 'tools', 'wallet', 'crypto_deposit', 'usd_wallet', 'deposit_status', 'giftcards', 'giftcard_status', 'giftcard_buy', 'giftcard_sell', 'buygiftcard', 'sellgiftcard'];

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
                $incomingState = $this->getState($chatId);
                $sensitiveInput = in_array($incomingState, [
                    'link_pin', 'reg_set_pin', 'reg_confirm_pin', 'sell_pin', 'buy_pin',
                    'reg_password', 'reg_confirm_password', 'giftcard_pin', 'giftcard_sale_code',
                ], true);
                $loggedText = $sensitiveInput ? '[secure input redacted]' : $text;

                Log::info('Received Telegram message', [
                    'chat_id'  => $chatId,
                    'username' => $username,
                    'text'     => substr($loggedText, 0, 100),
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
                    $loggedText ?: '[unknown]',
                    $messageType, $incomingState, $isCommand, $logFileId, $logFileName
                );
                if ($sensitiveInput && !empty($message['message_id'])) {
                    $this->deleteSensitiveMessage($chatId, (int) $message['message_id']);
                }

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
                    } elseif ($state === 'deposit_proof') {
                        $this->handleDepositProofUpload($chatId, $fileId);
                    } elseif ($state === 'upload_proof') {
                        $this->handleUploadProofSubmit($chatId, $fileId);
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
                if (preg_match('/\b(gift[\s-]?cards?|itunes cards?)\b/i', $text)) {
                    $command = preg_match('/\b(status|track|delivered|ready|check|where)\b/i', $text) ? 'giftcard_status'
                        : (preg_match('/\b(sell|selling|trade|trading)\b/i', $text) ? 'giftcard_sell' : (preg_match('/\b(buy|purchase|start|want|need)\b/i', $text) ? 'giftcard_buy' : 'giftcards'));
                } else {
                    $command = $this->resolveCommand($text);
                }
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
                        'deposit'  => $this->handleDepositCommand($chatId),
                        'balance'  => $this->handleBalanceCommand($chatId),
                    'trades', 'history', 'transactions' => $this->handleTradesCommand($chatId),
                        'verify'   => $this->handleVerifyCommand($chatId, $text, $username),
                        'status'   => $this->handleVerifyCommand($chatId, $text, $username),
                        'help'     => $this->handleHelpCommand($chatId),
                        'ai'       => $this->handleAiChatCommand($chatId),
                        'upload'   => $this->handleUploadCommand($chatId),
                        'features' => $this->handleFeaturesCommand($chatId),
                        'tools'    => $this->handleFeatureLink($chatId, 'Digital tools', '/tools'),
                        'withdraw' => $this->handleFeatureLink($chatId, 'Withdraw', '/wallet/withdraw'),
                        'wallet'   => $this->handleFeatureLink($chatId, 'Wallet overview', '/wallet'),
                        'crypto_deposit' => $this->handleCryptoDepositCommand($chatId),
                        'usd_wallet' => $this->handleUsdWalletCommand($chatId),
                        'deposit_status' => $this->handleCryptoDepositStatus($chatId),
                        'giftcards' => $this->handleGiftCardsCommand($chatId),
                        'giftcard_status' => $this->handleGiftCardStatusCommand($chatId, $text),
                        'giftcard_buy' => $this->handleGiftCardPurchaseStart($chatId),
                        'giftcard_sell' => $this->handleGiftCardSaleStart($chatId),
                        'buygiftcard' => $this->handleGiftCardPurchaseStart($chatId),
                        'sellgiftcard' => $this->handleGiftCardSaleStart($chatId),
                        default    => $this->handleUnknownCommand($chatId),
                    };
                    return true;
                }

                // ── State-aware text input ────────────────────────────────────────
                $state = $this->getState($chatId);
                if ($state !== null) {
                    switch ($state) {
                        case 'ai_chat':
                            $this->handleAiChatInput($chatId, $text);
                            return true;
                        case 'upload_ref':
                            $this->handleUploadRefInput($chatId, $text);
                            return true;
                        case 'sell_amount':
                            $this->handleSellAmountInput($chatId, $text);
                            return true;
                        case 'sell_ext_bank':
                            // Legacy free-text flow (Bank Name | Account Number | Account Name)
                            $this->handleSellExtBankInput($chatId, $text);
                            return true;
                        case 'sell_ext_bank_account':
                            $this->handleSellExtBankAccountInput($chatId, $text);
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
                        case 'sell_txid':
                            $this->handleSellTxidInput($chatId, $text);
                            return true;
                        case 'sell_sender_wallet':
                            $this->handleSellSenderWalletInput($chatId, $text);
                            return true;
                        case 'buy_txid':
                            $this->handleBuyTxidInput($chatId, $text);
                            return true;
                        case 'buy_pin':
                            $this->handleBuyPinInput($chatId, $text);
                            return true;
                        case 'deposit_amount':
                            $this->handleDepositAmountInput($chatId, $text);
                            return true;
                        case 'crypto_deposit_amount':
                            $this->handleCryptoDepositAmountInput($chatId, $text);
                            return true;
                        case 'giftcard_face_value':
                            $this->handleGiftCardFaceValueInput($chatId, $text);
                            return true;
                        case 'giftcard_recipient_email':
                            $this->handleGiftCardRecipientInput($chatId, $text);
                            return true;
                        case 'giftcard_note':
                            $this->handleGiftCardNoteInput($chatId, $text);
                            return true;
                        case 'giftcard_pin':
                            $this->handleGiftCardPinInput($chatId, $text);
                            return true;
                        case 'giftcard_sale_face_value':
                            $this->handleGiftCardSaleFaceValue($chatId, $text);
                            return true;
                        case 'giftcard_sale_note':
                            $this->handleGiftCardSaleNote($chatId, $text);
                            return true;
                        case 'giftcard_sale_code':
                            $this->handleGiftCardSaleCode($chatId, $text);
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

                if (str_starts_with($data, 'crypto_coin:')) {
                    $this->handleCryptoDepositCoinSelected($chatId, substr($data, 11));
                    return true;
                }
                if (str_starts_with($data, 'crypto_network:')) {
                    $this->handleCryptoDepositNetworkSelected($chatId, substr($data, 15));
                    return true;
                }
                if (str_starts_with($data, 'crypto_settlement:')) {
                    $this->handleCryptoDepositSettlementSelected($chatId, substr($data, 18));
                    return true;
                }
                if (str_starts_with($data, 'giftcard_rate:')) {
                    $this->handleGiftCardRateSelected($chatId, (int) substr($data, 14));
                    return true;
                }
                if (str_starts_with($data, 'giftcard_denom:')) {
                    $this->handleGiftCardDenominationSelected($chatId, (float) substr($data, 15));
                    return true;
                }
                if ($data === 'giftcard_note_skip') {
                    $this->mergeData($chatId, ['giftcard_note' => null]);
                    $this->showGiftCardConfirmation($chatId);
                    return true;
                }
                if ($data === 'giftcard_confirm') {
                    $this->setState($chatId, 'giftcard_pin');
                    $this->sendMessage($chatId, "🔐 *Confirm gift card purchase*\nEnter your 4-digit transaction PIN. Your message is redacted from bot logs and deleted when Telegram allows. The charge is only reserved after the PIN is verified.\n\n_Type /cancel to stop._");
                    return true;
                }
                if ($data === 'giftcard_buy') {
                    $this->handleGiftCardPurchaseStart($chatId);
                    return true;
                }
                if ($data === 'giftcard_sell') {
                    $this->handleGiftCardSaleStart($chatId);
                    return true;
                }
                if (str_starts_with($data, 'giftcard_sale_rate:')) {
                    $this->handleGiftCardSaleRateSelected($chatId, (int) substr($data, 19));
                    return true;
                }
                if ($data === 'giftcard_sale_note_skip') {
                    $this->mergeData($chatId, ['giftcard_sale_note' => null]);
                    $this->setState($chatId, 'giftcard_sale_code');
                    $this->sendMessage($chatId, 'Enter the card code to submit for review. It is stored encrypted and removed from Telegram chat where possible. No funds are credited until admin approval.');
                    return true;
                }
                if ($data === 'giftcard_sale_confirm') {
                    $this->handleGiftCardSaleConfirm($chatId);
                    return true;
                }

                // ── Sell flow callbacks ────────────────────────────────────────────
                if (str_starts_with($data, 'sell_amount_action:')) {
                    $this->handleSellAmountAction($chatId, substr($data, 19));
                    return true;
                }
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

                // External bank selection via Paystack bank list
                if (str_starts_with($data, 'sell_ext_bank_select:')) {
                    $payload = substr($data, strlen('sell_ext_bank_select:'));
                    // expected: {bank_code}|{bank_name}
                    $parts = explode('|', $payload, 2);
                    if (count($parts) === 2) {
                        $this->handleSellExternalBankSelected($chatId, $parts[0], $parts[1]);
                        return true;
                    }

                    $this->sendMessage($chatId, "⚠️ Invalid bank selection. Please try again with /sell.");
                    return true;
                }
                if (str_starts_with($data, 'sell_proof_method:')) {
                    $this->handleSellProofMethodSelected($chatId, substr($data, 18));
                    return true;
                }
                if ($data === 'sell_confirm') {
                    $this->handleSellConfirm($chatId);
                    return true;
                }
                if ($data === 'sell_sender_wallet_skip') {
                    $sellData = $this->getData($chatId);
                    $method = (string) ($sellData['payout_method'] ?? '');
                    $this->mergeData($chatId, ['sender_wallet_address' => null, 'sender_wallet_step_done' => true]);
                    if ($method !== '') $this->handleSellPayoutSelected($chatId, $method);
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
                if (str_starts_with($data, 'buy_proof_method:')) {
                    $this->handleBuyProofMethodSelected($chatId, substr($data, 17));
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
                if (str_starts_with($data, 'deposit_account:')) {
                    $this->handleDepositAccountSelected($chatId, (int) substr($data, 16));
                    return true;
                }
                if (str_starts_with($data, 'approve_deposit:')) {
                    $this->handleAdminApproveDeposit($chatId, (int) substr($data, 16), $messageId);
                    return true;
                }
                if (str_starts_with($data, 'reject_deposit:')) {
                    $this->handleAdminRejectDeposit($chatId, (int) substr($data, 15), $messageId);
                    return true;
                }

                // ── Legacy command callbacks ───────────────────────────────────────
                match ($data) {
                    'cmd_start'    => $this->handleStartCommand($chatId, $firstName),
                    'cmd_register' => $this->handleRegisterCommand($chatId),
                    'cmd_rates'    => $this->handleRatesCommand($chatId),
                    'cmd_buy'      => $this->handleBuyCommand($chatId),
                    'cmd_sell'     => $this->handleSellCommand($chatId),
                    'cmd_deposit'  => $this->handleDepositCommand($chatId),
                    'cmd_balance'  => $this->handleBalanceCommand($chatId),
                    'cmd_trades'   => $this->handleTradesCommand($chatId),
                    'cmd_help'     => $this->handleHelpCommand($chatId),
                    'cmd_ai'       => $this->handleAiChatCommand($chatId),
                    'cmd_crypto_deposit' => $this->handleCryptoDepositCommand($chatId),
                    'cmd_usd_wallet' => $this->handleUsdWalletCommand($chatId),
                    'cmd_crypto_status' => $this->handleCryptoDepositStatus($chatId),
                    'cmd_giftcards' => $this->handleGiftCardsCommand($chatId),
                    'cmd_giftcard_status' => $this->handleGiftCardStatusCommand($chatId, ''),
                    'cmd_giftcard_buy' => $this->handleGiftCardPurchaseStart($chatId),
                    'cmd_giftcard_sell' => $this->handleGiftCardSaleStart($chatId),
                    'cmd_wallet_page' => $this->handleFeatureLink($chatId, 'Wallet overview', '/wallet'),
                    'cmd_withdraw' => $this->handleFeatureLink($chatId, 'Withdraw', '/wallet/withdraw'),
                    'cmd_swap' => $this->handleFeatureLink($chatId, 'Swap crypto', '/swap'),
                    'cmd_bills' => $this->handleFeatureLink($chatId, 'Bills and utilities', '/bills'),
                    'cmd_portfolio' => $this->handleFeatureLink($chatId, 'Portfolio', '/portfolio'),
                    'cmd_support' => $this->handleFeatureLink($chatId, 'Support cases', '/support/cases'),
                    'cmd_history' => $this->handleFeatureLink($chatId, 'Wallet history', '/wallet/history'),
                    'cmd_transactions' => $this->handleFeatureLink($chatId, 'Transaction history', '/transactions/history'),
                    'cmd_activity' => $this->handleFeatureLink($chatId, 'Recent account activity', '/account/activity'),
                    'cmd_notifications' => $this->handleFeatureLink($chatId, 'Notifications', '/notifications'),
                    'cmd_tools' => $this->handleFeatureLink($chatId, 'Digital tools', '/tools'),
                    'cmd_price_alerts' => $this->handleFeatureLink($chatId, 'Price alerts', '/price-alerts'),
                    'cmd_forum' => $this->handleFeatureLink($chatId, 'Community forum', '/forum'),
                    'cmd_features' => $this->handleFeaturesCommand($chatId),
                    default        => null,
                };
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Error processing Telegram update', [
                'error' => $e->getMessage(),
                'update_id' => $update['update_id'] ?? null,
                'chat_id' => $update['message']['chat']['id'] ?? $update['callback_query']['message']['chat']['id'] ?? null,
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
                       "• `/features` — Open platform features\n" .
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
                    [
                        ['text' => '🤖 AI Assistant', 'callback_data' => 'cmd_ai'],
                    ],
                    [
                        $this->linkButton('₿ Deposit Crypto', '/wallet/deposit', 'cmd_crypto_deposit'),
                        $this->linkButton('💵 USD Wallet', '/wallet/usd', 'cmd_usd_wallet'),
                    ],
                    [
                        $this->linkButton('👛 Wallets', '/wallet', 'cmd_wallet_page'),
                        $this->linkButton('🔁 Swap', '/swap', 'cmd_swap'),
                    ],
                    [
                        $this->linkButton('🧾 Bills', '/bills', 'cmd_bills'),
                        $this->linkButton('🎁 Gift Cards', '/gift-cards', 'cmd_giftcards'),
                    ],
                    [
                        $this->linkButton('📈 Portfolio', '/portfolio', 'cmd_portfolio'),
                        $this->linkButton('🛟 Support', '/support/cases', 'cmd_support'),
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
                [
                    $this->linkButton('💱 Explore KayXchange', '/rate-calculator', 'cmd_features'),
                    $this->linkButton('🎁 Gift Cards', '/gift-cards', 'cmd_features'),
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

    /** Present current platform tools through secure, authenticated web destinations. */
    private function handleFeaturesCommand(int $chatId): void
    {
        $giftPurchaseEnabled = filter_var(\App\Models\AdminSetting::getSetting('gift_cards_enabled', '0'), FILTER_VALIDATE_BOOLEAN);
        $giftSalesEnabled = filter_var(\App\Models\AdminSetting::getSetting('gift_card_sales_enabled', '0'), FILTER_VALIDATE_BOOLEAN);
        $giftRows = [];
        if ($giftPurchaseEnabled || $giftSalesEnabled) {
            $giftButtons = [];
            if ($giftPurchaseEnabled) $giftButtons[] = $this->linkButton('🎁 Buy gift cards', '/gift-cards', 'cmd_giftcard_buy');
            if ($giftSalesEnabled) $giftButtons[] = $this->linkButton('💳 Sell gift cards', '/gift-cards', 'cmd_giftcard_sell');
            $giftRows[] = $giftButtons;
        }

        $user = User::where('telegram_chat_id', $chatId)->first();
        $message = $user
            ? "✨ *KayXchange features*\nChoose a service below. Wallet actions open the secure website so your account session and payment review rules remain active."
            : "✨ *Explore KayXchange*\nLink or create your account to use wallet and trading services. Crypto deposit invoices and payment tracking use the platform’s current configuration.";

        $keyboard = ['inline_keyboard' => [
            [
                $this->linkButton('₿ Crypto deposit', '/wallet/deposit', 'cmd_crypto_deposit'),
                $this->linkButton('💵 USD wallet', '/wallet/usd', 'cmd_usd_wallet'),
            ],
            [
                $this->linkButton('👛 Wallet overview', '/wallet', 'cmd_wallet_page'),
                $this->linkButton('💸 Withdraw', '/wallet/withdraw', 'cmd_withdraw'),
            ],
            [
                $this->linkButton('🔁 Swap', '/swap', 'cmd_swap'),
                $this->linkButton('🧾 Bills', '/bills', 'cmd_bills'),
            ],
            ...$giftRows,
            [
                $this->linkButton('📈 Portfolio', '/portfolio', 'cmd_portfolio'),
                $this->linkButton('🔔 Price alerts', '/price-alerts', 'cmd_price_alerts'),
            ],
            [
                $this->linkButton('📜 Wallet history', '/wallet/history', 'cmd_history'),
                $this->linkButton('🛟 Support', '/support/cases', 'cmd_support'),
            ],
            [
                $this->linkButton('🧾 Transactions', '/transactions/history', 'cmd_transactions'),
                $this->linkButton('🔔 Notifications', '/notifications', 'cmd_notifications'),
            ],
            [
                $this->linkButton('🕘 Account activity', '/account/activity', 'cmd_activity'),
                $this->linkButton('💬 Community forum', '/forum', 'cmd_forum'),
            ],
            [
                $this->linkButton('🧰 Digital tools', '/tools', 'cmd_tools'),
                $this->linkButton('📋 All transactions', '/transactions/history', 'cmd_transactions'),
            ],
            [
                ['text' => '📊 Live rates', 'callback_data' => 'cmd_rates'],
                ['text' => '🤖 Ask KayX AI', 'callback_data' => 'cmd_ai'],
            ],
        ]];

        $this->sendMessage($chatId, $message, 'Markdown', $keyboard);
    }

    private function handleFeatureLink(int $chatId, string $label, string $path): void
    {
        $destination = $this->telegramAppUrl() . $path;
        $message = "🔐 *{$label}*\nOpen the secure KayXchange page to continue. Sign in if requested.\n\n" . $destination;
        $keyboard = null;
        if ($this->shouldUseUrlButtons()) {
            $keyboard = ['inline_keyboard' => [[['text' => 'Open ' . $label, 'url' => $destination]], [['text' => '✨ All features', 'callback_data' => 'cmd_features']]]];
        }
        $this->sendMessage($chatId, $message, 'Markdown', $keyboard);
    }

    private function handleCryptoDepositCommand(int $chatId): void
    {
        $user = User::where('telegram_chat_id', $chatId)->first();
        if (!$user) {
            $this->sendMessage($chatId, "🔐 Link your KayXchange account first, then use /crypto_deposit. Deposits can be settled into the USD crypto portfolio or your NGN wallet.", 'Markdown', ['inline_keyboard' => [[['text' => 'Create account', 'url' => $this->telegramAppUrl() . '/register']], [['text' => 'Sign in', 'url' => $this->telegramAppUrl() . '/login']]]]);
            return;
        }

        $cryptoWallets = app(CryptoWalletService::class);
        $supported = $cryptoWallets->getSupportedCryptos();
        $rows = [];
        foreach ($supported as $coin => $details) {
            $rows[] = [[
                'text' => ($details['name'] ?? $coin) . ' · ' . $coin,
                'callback_data' => 'crypto_coin:' . $coin,
            ]];
        }
        $this->setData($chatId, ['crypto_deposit_settlement' => 'usd']);
        $this->setState($chatId, 'crypto_deposit_coin');
        $this->sendMessage($chatId, "₿ *Create a crypto deposit*\nChoose the asset. You’ll select its network and USD invoice value next. The invoice uses the platform’s active payment configuration and status tracking. No balance is deducted to create an invoice.", 'Markdown', ['inline_keyboard' => $rows]);
    }

    private function handleUsdWalletCommand(int $chatId): void
    {
        $user = User::where('telegram_chat_id', $chatId)->first();
        $message = $user
            ? "💵 *USD crypto portfolio*\nView the live USD valuation of your supported crypto holdings and use the wallet page to manage available actions. This is a portfolio valuation, not a separate cash balance."
            : "💵 *USD crypto portfolio*\nSign in to view your live portfolio valuation across supported crypto holdings.";
        $this->sendMessage($chatId, $message, 'Markdown', ['inline_keyboard' => [[['text' => 'Open USD wallet', 'url' => $this->telegramAppUrl() . '/wallet/usd']], [['text' => 'Open crypto deposit', 'url' => $this->telegramAppUrl() . '/wallet/deposit']]]]);
    }

    private function handleCryptoDepositCoinSelected(int $chatId, string $coin): void
    {
        $supported = app(CryptoWalletService::class)->getSupportedCryptos();
        $coin = strtoupper(trim($coin));
        if (!isset($supported[$coin])) {
            $this->sendMessage($chatId, 'That asset is not currently supported for deposits. Use /crypto_deposit to choose an available asset.');
            return;
        }
        $this->mergeData($chatId, ['crypto_deposit_coin' => $coin]);
        $this->setState($chatId, 'crypto_deposit_network');
        $rows = array_map(fn ($network) => [[
            'text' => $coin . ' · ' . $network,
            'callback_data' => 'crypto_network:' . $network,
        ]], $supported[$coin]['networks']);
        $this->sendMessage($chatId, "Select the network for *{$coin}*. Send only this asset over the exact selected network.", 'Markdown', ['inline_keyboard' => $rows]);
    }

    private function handleCryptoDepositNetworkSelected(int $chatId, string $network): void
    {
        $data = $this->getData($chatId);
        $coin = strtoupper((string) ($data['crypto_deposit_coin'] ?? ''));
        $supported = app(CryptoWalletService::class)->getSupportedCryptos();
        $network = strtoupper(trim($network));
        if (!$coin || !in_array($network, $supported[$coin]['networks'] ?? [], true)) {
            $this->sendMessage($chatId, 'That network does not match the selected asset. Start again with /crypto_deposit.');
            return;
        }
        $this->mergeData($chatId, ['crypto_deposit_network' => $network]);
        $this->setState($chatId, 'crypto_deposit_settlement');
        $this->sendMessage($chatId, "Where should a confirmed deposit settle?\n\nUSD keeps the crypto in its coin wallet and updates your read-only crypto portfolio value. NGN converts the confirmed value into your Naira wallet.", 'Markdown', ['inline_keyboard' => [[['text' => '💵 USD crypto portfolio', 'callback_data' => 'crypto_settlement:usd']], [['text' => '₦ Naira wallet', 'callback_data' => 'crypto_settlement:ngn']]]]);
    }

    private function handleCryptoDepositSettlementSelected(int $chatId, string $preference): void
    {
        $preference = strtolower($preference);
        if (!in_array($preference, ['usd', 'ngn'], true)) {
            $this->sendMessage($chatId, 'Choose USD crypto portfolio or Naira wallet.');
            return;
        }
        $data = $this->getData($chatId);
        if (empty($data['crypto_deposit_coin']) || empty($data['crypto_deposit_network'])) {
            $this->sendMessage($chatId, 'Your deposit selection expired. Start again with /crypto_deposit.');
            $this->clearState($chatId);
            return;
        }
        $this->mergeData($chatId, ['crypto_deposit_settlement' => $preference]);
        $this->setState($chatId, 'crypto_deposit_amount');
        $settlementLabel = $preference === 'usd' ? 'USD crypto portfolio' : 'Naira wallet';
        $this->sendMessage($chatId, "Enter the invoice value in USD (minimum is set by the active provider).\n\nAsset: *{$data['crypto_deposit_coin']} · {$data['crypto_deposit_network']}*\nSettlement: *{$settlementLabel}*\n\n_No funds are debited when a deposit invoice is created._", 'Markdown');
    }

    private function handleCryptoDepositAmountInput(int $chatId, string $text): void
    {
        $user = User::where('telegram_chat_id', $chatId)->first();
        $data = $this->getData($chatId);
        $amount = is_numeric($text) ? (float) $text : 0;
        if (!$user || empty($data['crypto_deposit_coin']) || empty($data['crypto_deposit_network']) || $amount <= 0 || $amount > 100000) {
            $this->sendMessage($chatId, 'Enter a valid USD amount up to $100,000, or use /cancel to stop.');
            return;
        }

        $coin = strtoupper((string) $data['crypto_deposit_coin']);
        $network = strtoupper((string) $data['crypto_deposit_network']);
        $settlement = ($data['crypto_deposit_settlement'] ?? 'usd') === 'ngn' ? 'ngn' : 'usd';
        $walletService = app(CryptoWalletService::class);
        try {
            $walletService->getUserWallets($user);
            $market = app(ExchangeRateService::class)->getUsdRates();
            $marketPrice = in_array($coin, $market['_live_coins'] ?? [], true) ? (float) ($market[$coin] ?? 0) : 0.0;
            $result = $walletService->createCryptoDeposit($user, $coin, $amount, [
                'input_type' => 'usd', 'network' => $network, 'input_amount' => $amount,
                'input_currency' => 'USD', 'input_usd_amount' => round($amount, 8),
                'quoted_market_price_usd' => $marketPrice > 0 ? $marketPrice : null,
                'market_source' => $market['_source'] ?? null,
                'quoted_at' => $market['_updated_at'] ?? null,
                'settlement_preference' => $settlement,
                'quoted_ngn_per_usd' => app(CryptoTradeRateService::class)->rate($coin, 'sell'),
                'created_from' => 'telegram_kayx_bot',
            ]);
            $deposit = $result['deposit'];
            Cache::put('tg_latest_crypto_deposit_' . $chatId, $deposit->id, now()->addDays(7));
            $paymentData = $deposit->payment_data ?? [];
            $expectedCrypto = (float) data_get($paymentData, 'expected_crypto_amount', $result['expected_amount'] ?? 0);
            $payCurrency = strtoupper((string) data_get($paymentData, 'provider_pay_currency', $coin));
            $address = (string) ($deposit->payment_address ?? '');
            $expires = optional($deposit->expires_at)->timezone(config('app.timezone'))->format('d M, H:i');
            $message = "✅ *Deposit invoice created*\n\n" .
                "Invoice: *$" . number_format($amount, 2) . " USD*\n" .
                "Asset/network: *{$coin} · {$network}*\n" .
                "Settlement: *" . ($settlement === 'usd' ? 'USD crypto portfolio' : 'Naira wallet') . "*\n" .
                ($expectedCrypto > 0 ? "Send exactly: *" . rtrim(rtrim(number_format($expectedCrypto, 12, '.', ''), '0'), '.') . " {$payCurrency}*\n" : '') .
                ($address !== '' ? "Deposit address:\n`{$address}`\n" : '') .
                ($expires ? "Invoice window ends: {$expires}\n" : '') .
                "\nPayment is credited only after confirmation and any required admin review. Do not send after the invoice expires.";
            $keyboard = ['inline_keyboard' => [
                [['text' => '🔄 Check deposit status', 'callback_data' => 'cmd_crypto_status']],
                [['text' => 'Open status page', 'url' => $this->telegramAppUrl() . '/wallet/deposit/' . $deposit->id . '/success']],
            ]];
            $this->clearState($chatId);
            $this->sendMessage($chatId, $message, 'Markdown', $keyboard);
            if ($address !== '') {
                $barcode = $this->getBarcodePathForWallet($address);
                if ($barcode) {
                    $this->sendPhoto($chatId, $barcode, "Scan to copy the {$coin} {$network} deposit address.");
                    @unlink($barcode);
                }
            }
        } catch (\App\Services\DepositMinimumException $e) {
            $this->sendMessage($chatId, 'The minimum for this asset and network is $' . number_format($e->minimumUsd, 2) . ' USD. Enter a higher amount or use /cancel.');
        } catch (\Throwable $e) {
            Log::warning('Telegram crypto deposit creation failed', ['user_id' => $user->id, 'coin' => $coin, 'network' => $network, 'error' => $e->getMessage()]);
            $this->clearState($chatId);
            $this->sendMessage($chatId, 'Crypto deposits are temporarily unavailable. No balance was deducted. Please try again later or use the secure wallet page.');
        }
    }

    private function handleCryptoDepositStatus(int $chatId): void
    {
        $user = User::where('telegram_chat_id', $chatId)->first();
        $depositId = Cache::get('tg_latest_crypto_deposit_' . $chatId);
        $deposit = $user
            ? CryptoDeposit::query()->where('user_id', $user->id)->when($depositId, fn ($q) => $q->whereKey($depositId))->latest()->first()
            : null;
        if (!$deposit) {
            $this->sendMessage($chatId, 'No recent crypto deposit was found. Start one with /crypto_deposit.');
            return;
        }
        if (Cache::add('tg_crypto_status_poll_' . $chatId, 1, now()->addSeconds(8))) {
            try { $deposit = app(CryptoWalletService::class)->refreshDepositStatus($deposit); }
            catch (\Throwable $e) { Log::info('Telegram deposit status refresh deferred', ['deposit_id' => $deposit->id, 'error' => $e->getMessage()]); }
        }
        $status = strtolower((string) $deposit->status);
        $providerStatus = strtolower((string) data_get($deposit->payment_data, 'provider_status', $status));
        $received = (float) ($deposit->received_amount ?? 0);
        $remaining = data_get($deposit->payment_data, 'partial_remaining_crypto');
        $message = "₿ *Deposit status*\n" .
            "Deposit #{$deposit->id} · *" . strtoupper($deposit->currency) . "*\n" .
            "Status: *" . str_replace('_', ' ', ucfirst($providerStatus)) . "*\n" .
            ($received > 0 ? "Received: *" . number_format($received, 8, '.', '') . ' ' . strtoupper($deposit->currency) . "*\n" : '') .
            ($remaining !== null && (float) $remaining > 0 ? "Still due: *" . number_format((float) $remaining, 8, '.', '') . ' ' . strtoupper($deposit->currency) . "*\n" : '') .
            ($status === 'awaiting_admin_review' ? "\nPayment confirmed; waiting for admin review before wallet credit." : ($status === 'completed' ? "\nYour wallet credit is complete." : "\nThe page will update after provider confirmation."));
        $this->sendMessage($chatId, $message, 'Markdown', ['inline_keyboard' => [[['text' => 'Refresh status', 'callback_data' => 'cmd_crypto_status']], [['text' => 'Open deposit page', 'url' => $this->telegramAppUrl() . '/wallet/deposit/' . $deposit->id . '/success']]]]);
    }

    private function handleGiftCardsCommand(int $chatId): void
    {
        $user = User::where('telegram_chat_id', $chatId)->first();
        if (!$user) {
            $this->sendMessage($chatId, 'Link your KayXchange account to submit gift-card transactions. Sign in or register, then link Telegram from your profile.', 'Markdown', ['inline_keyboard' => [[['text' => 'Sign in', 'url' => $this->telegramAppUrl() . '/login']], [['text' => 'Register', 'url' => $this->telegramAppUrl() . '/register']]]]);
            return;
        }
        $purchaseEnabled = filter_var(\App\Models\AdminSetting::getSetting('gift_cards_enabled', '0'), FILTER_VALIDATE_BOOLEAN);
        $salesEnabled = filter_var(\App\Models\AdminSetting::getSetting('gift_card_sales_enabled', '0'), FILTER_VALIDATE_BOOLEAN);
        $buttons = [];
        if ($purchaseEnabled) $buttons[] = [['text' => '🎁 Buy a gift card', 'callback_data' => 'giftcard_buy']];
        if ($salesEnabled) $buttons[] = [['text' => '💳 Sell a gift card', 'callback_data' => 'giftcard_sell']];
        $buttons[] = [['text' => '🔎 Check purchase status', 'callback_data' => 'cmd_giftcard_status']];
        $prompt = ($purchaseEnabled || $salesEnabled)
            ? 'Choose an available USA or UK gift-card service:'
            : 'Gift-card trading is temporarily paused. You can still check the status of an existing purchase.';
        $this->sendMessage($chatId, $prompt, 'Markdown', ['inline_keyboard' => $buttons]);
    }

    private function handleGiftCardStatusCommand(int $chatId, string $query = ''): void
    {
        $user = User::where('telegram_chat_id', $chatId)->first();
        if (!$user) {
            $this->sendMessage($chatId, 'Link your KayXchange account first to check your private gift-card purchases.');
            return;
        }
        $message = app(\App\Services\GiftCardStatusService::class)->forUser($user, $query);
        $this->sendMessage($chatId, $message, null, ['inline_keyboard' => [[['text' => 'Open gift-card history', 'url' => $this->telegramAppUrl() . '/gift-cards']]]]);
    }

    private function handleGiftCardPurchaseStart(int $chatId): void
    {
        $user = User::where('telegram_chat_id', $chatId)->first();
        if (!$user) {
            $this->sendMessage($chatId, 'Link your KayXchange account before buying a gift card. Sign in or register, then use /giftcards.', 'Markdown', ['inline_keyboard' => [[['text' => 'Sign in', 'url' => $this->telegramAppUrl() . '/login']], [['text' => 'Register', 'url' => $this->telegramAppUrl() . '/register']]]]);
            return;
        }
        if (!filter_var(\App\Models\AdminSetting::getSetting('gift_cards_enabled', '0'), FILTER_VALIDATE_BOOLEAN)) {
            $this->sendMessage($chatId, 'Gift card purchases are temporarily paused by the platform.');
            return;
        }
        $rates = GiftCardRate::query()->where('is_active', true)->whereIn('country', ['US', 'UK'])
            ->where('sell_rate', '>', 0)->where('usd_exchange_rate', '>', 0)->orderBy('country')->orderBy('name')->limit(40)->get();
        if ($rates->isEmpty()) {
            $this->sendMessage($chatId, 'USA and UK gift card rates are not available right now. Please check again later.');
            return;
        }
        $rows = [];
        foreach ($rates as $rate) {
            $rows[] = [[
                'text' => $rate->name . ' · ' . $rate->country . ' ' . $rate->currency,
                'callback_data' => 'giftcard_rate:' . $rate->id,
            ]];
        }
        $this->clearState($chatId);
        $this->setState($chatId, 'giftcard_face_value');
        $this->sendMessage($chatId, "🎁 *Buy a USA or UK gift card*\nChoose the card type. The next step shows the face value in USD/GBP, the platform conversion rate, the admin-set service fee and the exact Naira total. Your Naira wallet is charged only after PIN confirmation; fulfillment is reviewed by the admin.", 'Markdown', ['inline_keyboard' => $rows]);
    }

    private function handleGiftCardSaleStart(int $chatId): void
    {
        $user = User::where('telegram_chat_id', $chatId)->first();
        if (!$user) {
            $this->sendMessage($chatId, 'Link your KayXchange account before submitting a card for sale. Use /giftcards after linking.');
            return;
        }
        if (!filter_var(\App\Models\AdminSetting::getSetting('gift_card_sales_enabled', '0'), FILTER_VALIDATE_BOOLEAN)) {
            $this->sendMessage($chatId, 'Gift card selling is currently paused by the platform.', 'Markdown', ['inline_keyboard' => [[['text' => 'Buy a gift card', 'callback_data' => 'giftcard_buy']]]]);
            return;
        }
        $rates = GiftCardRate::query()->where('is_active', true)->whereIn('country', ['US','UK'])->where('buy_rate', '>', 0)
            ->orderBy('country')->orderBy('name')->limit(40)->get();
        if ($rates->isEmpty()) {
            $this->sendMessage($chatId, 'Gift card selling is enabled, but no USA/UK cards currently have an active sell rate.');
            return;
        }
        $rows = [];
        foreach ($rates as $rate) $rows[] = [['text' => $rate->name . ' · ' . $rate->country . ' · ₦' . number_format((float)$rate->buy_rate, 2) . '/' . $rate->currency, 'callback_data' => 'giftcard_sale_rate:' . $rate->id]];
        $this->setData($chatId, ['giftcard_sale_token' => (string) Str::uuid()]);
        $this->setState($chatId, 'giftcard_sale_face_value');
        $this->sendMessage($chatId, 'Choose the USA/UK gift card you want to sell. The displayed rate is an estimate; admin verifies the card and approves the final NGN credit.', 'Markdown', ['inline_keyboard' => $rows]);
    }

    private function handleGiftCardSaleRateSelected(int $chatId, int $rateId): void
    {
        $rate = GiftCardRate::query()->whereKey($rateId)->where('is_active', true)->whereIn('country', ['US','UK'])->where('buy_rate', '>', 0)->first();
        if (!$rate || !filter_var(\App\Models\AdminSetting::getSetting('gift_card_sales_enabled', '0'), FILTER_VALIDATE_BOOLEAN)) {
            $this->sendMessage($chatId, 'That gift card is not currently accepted for selling. Refresh with /giftcards.');
            return;
        }
        $this->mergeData($chatId, ['giftcard_sale_rate_id' => $rate->id]);
        $this->setState($chatId, 'giftcard_sale_face_value');
        $this->sendMessage($chatId, "Selected {$rate->name} · {$rate->country}. Enter the card face value in {$rate->currency} (1–10,000). Estimated payout uses ₦" . number_format((float)$rate->buy_rate, 2) . " per {$rate->currency}; admin confirms after review.");
    }

    private function handleGiftCardSaleFaceValue(int $chatId, string $text): void
    {
        $value = is_numeric(str_replace(',', '', trim($text))) ? (float)str_replace(',', '', trim($text)) : 0;
        $data = $this->getData($chatId);
        $rate = GiftCardRate::query()->whereKey($data['giftcard_sale_rate_id'] ?? 0)->where('is_active', true)->where('buy_rate', '>', 0)->first();
        if (!$rate || $value < 1 || $value > 10000) {
            $this->sendMessage($chatId, 'Enter a face value from 1 to 10,000, or use /cancel.');
            return;
        }
        $this->mergeData($chatId, ['giftcard_sale_face_value' => round($value, 2)]);
        $this->setState($chatId, 'giftcard_sale_note');
        $this->sendMessage($chatId, 'Add an optional note for admin verification or tap Skip.', 'Markdown', ['inline_keyboard' => [[['text' => 'Skip note', 'callback_data' => 'giftcard_sale_note_skip']]]]);
    }

    private function handleGiftCardSaleNote(int $chatId, string $text): void
    {
        $this->mergeData($chatId, ['giftcard_sale_note' => in_array(mb_strtolower(trim($text)), ['skip','no','none'], true) ? null : mb_substr(trim($text), 0, 500)]);
        $this->setState($chatId, 'giftcard_sale_code');
        $this->sendMessage($chatId, 'Enter the card code to submit for admin verification. It is stored encrypted and this Telegram message is removed when possible. Your NGN wallet is not credited until admin approval.');
    }

    private function handleGiftCardSaleCode(int $chatId, string $text): void
    {
        $code = trim($text);
        if (mb_strlen($code) < 4 || mb_strlen($code) > 2000) {
            $this->sendMessage($chatId, 'Enter a valid gift-card code, or use /cancel.');
            return;
        }
        $this->mergeData($chatId, ['giftcard_sale_code' => \Illuminate\Support\Facades\Crypt::encryptString($code)]);
        $data = $this->getData($chatId);
        $rate = GiftCardRate::find($data['giftcard_sale_rate_id'] ?? 0);
        if (!$rate) {
            $this->clearState($chatId);
            $this->setData($chatId, []);
            $this->sendMessage($chatId, 'That gift card is no longer available. Start again with /giftcards.');
            return;
        }
        $estimate = (float)($data['giftcard_sale_face_value'] ?? 0) * (float)$rate->buy_rate;
        $this->setState($chatId, 'giftcard_sale_confirm');
        $this->sendMessage($chatId, "Review gift card sale\nCard: {$rate->name} · {$rate->country}\nFace value: {$rate->currency} " . number_format((float)$data['giftcard_sale_face_value'], 2) . "\nEstimated value: ₦" . number_format($estimate, 2) . "\n\nAdmin verifies the code before approval. No balance will be credited until then.", 'Markdown', ['inline_keyboard' => [[['text' => '✅ Submit for review', 'callback_data' => 'giftcard_sale_confirm']], [['text' => 'Cancel', 'callback_data' => 'cancel']]]]);
    }

    private function handleGiftCardSaleConfirm(int $chatId): void
    {
        $user = User::where('telegram_chat_id', $chatId)->first();
        $data = $this->getData($chatId);
        if (!$user || empty($data['giftcard_sale_code'])) {
            $this->clearState($chatId);
            $this->setData($chatId, []);
            $this->sendMessage($chatId, 'This gift card request expired. Start again with /giftcards.');
            return;
        }
        try {
            $order = app(\App\Services\GiftCardSaleService::class)->submit(
                $user, (int)($data['giftcard_sale_rate_id'] ?? 0), (float)($data['giftcard_sale_face_value'] ?? 0),
                \Illuminate\Support\Facades\Crypt::decryptString((string)$data['giftcard_sale_code']), $data['giftcard_sale_note'] ?? null, (string)($data['giftcard_sale_token'] ?? '')
            );
            $this->clearState($chatId);
            $this->setData($chatId, []);
            $this->sendMessage($chatId, "✅ Gift-card sale submitted for review\nReference: {$order->reference}\nEstimated value: ₦" . number_format((float)$order->estimated_ngn, 2) . "\nStatus: Pending admin verification. The final NGN amount is credited only after approval.", 'Markdown', ['inline_keyboard' => [[['text' => 'Gift card history', 'url' => $this->telegramAppUrl() . '/gift-cards']], [['text' => 'Buy a gift card', 'callback_data' => 'giftcard_buy']]]]);
        } catch (\Throwable $e) {
            Log::error('Telegram gift-card sale submit failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            $this->clearState($chatId);
            $this->setData($chatId, []);
            $this->sendMessage($chatId, 'We could not submit the card. Please contact support or try the secure Gift Cards page.');
        }
    }

    private function handleGiftCardRateSelected(int $chatId, int $rateId): void
    {
        $rate = GiftCardRate::query()->whereKey($rateId)->where('is_active', true)->whereIn('country', ['US', 'UK'])
            ->where('sell_rate', '>', 0)->where('usd_exchange_rate', '>', 0)->first();
        if (!$rate || !filter_var(\App\Models\AdminSetting::getSetting('gift_cards_enabled', '0'), FILTER_VALIDATE_BOOLEAN)) {
            $this->sendMessage($chatId, 'That gift card is not currently available. Use /giftcards to refresh the list.');
            return;
        }
        $token = (string) Str::uuid();
        $this->setData($chatId, ['giftcard_rate_id' => $rate->id, 'giftcard_token' => $token]);
        $this->setState($chatId, 'giftcard_face_value');
        if (\App\Services\GiftCardPurchaseService::isDenominationCard($rate)) {
            $rows = [];
            $row = [];
            $purchaseService = app(GiftCardPurchaseService::class);
            foreach ($purchaseService->denominationsFor($rate) as $denomination) {
                $quote = $purchaseService->quote($rate, (float) $denomination);
                $row[] = [
                    'text' => '$' . number_format($denomination, 0) . ' · ₦' . number_format($quote['base_total_ngn'], 0),
                    'callback_data' => 'giftcard_denom:' . $denomination,
                ];
                if (count($row) === 3) { $rows[] = $row; $row = []; }
            }
            if ($row) $rows[] = $row;
            $this->sendMessage($chatId, "Selected: *{$rate->name} · USA*\nChoose a supported face value. Prices use ₦" . number_format($purchaseService->quote($rate, 2)['ngn_per_usd'], 2) . " per USD. Admin service fee ₦" . number_format((float) ($rate->purchase_markup_ngn ?? 0), 2) . " is added to the total at review.", 'Markdown', ['inline_keyboard' => $rows]);
            return;
        }
        $this->sendMessage($chatId, "Selected: *{$rate->name} · {$rate->country}*\nEnter the card face value in *{$rate->currency}* (between 1 and 10,000). Example: `100`.", 'Markdown');
    }

    private function handleGiftCardDenominationSelected(int $chatId, float $denomination): void
    {
        $data = $this->getData($chatId);
        $rate = GiftCardRate::query()->whereKey($data['giftcard_rate_id'] ?? 0)->where('is_active', true)->first();
        if (!$rate || !\App\Services\GiftCardPurchaseService::isValidDenomination($rate, $denomination)) {
            $this->sendMessage($chatId, 'That denomination is not available for this card. Start again with /giftcards.');
            return;
        }
        $quote = app(GiftCardPurchaseService::class)->quote($rate, $denomination);
        $this->mergeData($chatId, ['giftcard_face_value' => $denomination]);
        $this->setState($chatId, 'giftcard_recipient_email');
        $this->sendMessage($chatId, "Selected *$" . number_format($denomination, 2) . "* · Base value ₦" . number_format($quote['base_total_ngn'], 2) . " · Service fee ₦" . number_format($quote['service_fee_ngn'], 2) . " · Total ₦" . number_format($quote['total_ngn'], 2) . "\n\nEnter the recipient email address.", 'Markdown');
    }

    private function handleGiftCardFaceValueInput(int $chatId, string $text): void
    {
        $value = is_numeric($text) ? (float) $text : 0;
        $data = $this->getData($chatId);
        $rate = GiftCardRate::query()->whereKey($data['giftcard_rate_id'] ?? 0)->where('is_active', true)->first();
        if (!$rate || $value < 1 || $value > 10000) {
            $this->sendMessage($chatId, 'Enter a face value from 1 to 10,000, or use /cancel to stop.');
            return;
        }
        if (!\App\Services\GiftCardPurchaseService::isValidDenomination($rate, $value)) {
            $this->sendMessage($chatId, 'Please choose one of the listed USA Apple denominations using the buttons above.');
            return;
        }
        $this->mergeData($chatId, ['giftcard_face_value' => round($value, 2)]);
        $this->setState($chatId, 'giftcard_recipient_email');
        $this->sendMessage($chatId, "Enter the recipient email for this *{$rate->name} {$rate->country}* card.", 'Markdown');
    }

    private function handleGiftCardRecipientInput(int $chatId, string $email): void
    {
        if (!filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
            $this->sendMessage($chatId, 'Enter a valid recipient email address.');
            return;
        }
        $this->mergeData($chatId, ['giftcard_recipient' => trim($email)]);
        $this->setState($chatId, 'giftcard_note');
        $this->sendMessage($chatId, 'Add an optional note for the fulfillment team, or tap Skip.', 'Markdown', ['inline_keyboard' => [[['text' => 'Skip note', 'callback_data' => 'giftcard_note_skip']]]]);
    }

    private function handleGiftCardNoteInput(int $chatId, string $text): void
    {
        $this->mergeData($chatId, ['giftcard_note' => mb_substr(trim($text), 0, 500)]);
        $this->showGiftCardConfirmation($chatId);
    }

    private function showGiftCardConfirmation(int $chatId): void
    {
        $data = $this->getData($chatId);
        $user = User::where('telegram_chat_id', $chatId)->first();
        $rate = GiftCardRate::query()->whereKey($data['giftcard_rate_id'] ?? 0)->where('is_active', true)->first();
        if (!$user || !$rate || !isset($data['giftcard_face_value'], $data['giftcard_recipient'])) {
            $this->clearState($chatId);
            $this->sendMessage($chatId, 'This gift card request expired. Start again with /giftcards.');
            return;
        }
        $quote = app(GiftCardPurchaseService::class)->quote($rate, (float) $data['giftcard_face_value']);
        $limit = (float) \App\Models\AdminSetting::getSetting('gift_card_purchase_per_order_limit_ngn', 250000);
        if ($quote['total_ngn'] > $limit) {
            $this->sendMessage($chatId, 'This amount is above the current per-order limit of ₦' . number_format($limit, 2) . '. Choose a smaller face value with /giftcards.');
            $this->clearState($chatId);
            return;
        }
        if ((float) $user->balance < $quote['total_ngn']) {
            $this->sendMessage($chatId, 'Your Naira wallet balance is below the total charge of ₦' . number_format($quote['total_ngn'], 2) . '. Deposit NGN and try again.');
            $this->clearState($chatId);
            return;
        }
        $this->mergeData($chatId, ['giftcard_expected_total_ngn' => (float) $quote['total_ngn']]);
        $currency = htmlspecialchars((string) $rate->currency, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $cardName = htmlspecialchars((string) $rate->name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $country = htmlspecialchars((string) $rate->country, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $email = htmlspecialchars((string) $data['giftcard_recipient'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html = "<b>Review gift card purchase</b>\n\n" .
            "<b>Card:</b> {$cardName} · {$country}\n" .
            "<b>Face value:</b> {$currency} " . number_format((float) $data['giftcard_face_value'], 2) . "\n" .
            "<b>Calculated at:</b> ₦" . number_format($quote['ngn_per_usd'], 2) . " / USD\n" .
            "<b>Service fee:</b> ₦" . number_format($quote['service_fee_ngn'], 2) . "\n" .
            "<b>Total charge:</b> ₦" . number_format($quote['total_ngn'], 2) . "\n" .
            "<b>Recipient:</b> {$email}\n" .
            "<b>Wallet balance:</b> ₦" . number_format((float) $user->balance, 2) . "\n\n" .
            "This creates a pending fulfillment request and reserves the displayed NGN amount after PIN confirmation.";
        $this->setState($chatId, 'giftcard_review');
        $this->sendMessage($chatId, $html, 'HTML', ['inline_keyboard' => [[['text' => '🔐 Confirm and enter PIN', 'callback_data' => 'giftcard_confirm']], [['text' => 'Cancel', 'callback_data' => 'cancel']]]]);
    }

    private function handleGiftCardPinInput(int $chatId, string $pin): void
    {
        if (!preg_match('/^\d{4}$/', trim($pin))) {
            $this->sendMessage($chatId, 'Enter your 4-digit transaction PIN, or use /cancel to stop.');
            return;
        }
        $data = $this->getData($chatId);
        $user = User::where('telegram_chat_id', $chatId)->first();
        if (!$user) {
            $this->clearState($chatId);
            $this->sendMessage($chatId, 'Your Telegram account link is no longer active. Link your account and try again.');
            return;
        }
        try {
            $order = app(GiftCardPurchaseService::class)->purchase(
                $user, (int) ($data['giftcard_rate_id'] ?? 0), (float) ($data['giftcard_face_value'] ?? 0),
                (string) ($data['giftcard_recipient'] ?? ''), trim($pin), $data['giftcard_note'] ?? null,
                (string) ($data['giftcard_token'] ?? ''), (float) ($data['giftcard_expected_total_ngn'] ?? 0)
            );
            $this->clearState($chatId);
            $this->sendMessage($chatId, "✅ *Gift card request received*\n\nReference: `{$order->reference}`\nCard: {$order->card_name} · {$order->country}\nFace value: {$order->currency} " . number_format((float) $order->face_value, 2) . "\nReserved: ₦" . number_format((float) $order->total_ngn, 2) . "\nStatus: *Pending fulfillment review*\n\nWe’ll notify you when it is ready. Your order is available in Gift Cards → Purchase history.", 'Markdown', ['inline_keyboard' => [[['text' => 'Gift card history', 'url' => $this->telegramAppUrl() . '/gift-cards']], [['text' => 'All features', 'callback_data' => 'cmd_features']]]]);
        } catch (\App\Exceptions\GiftCardPurchaseException $e) {
            if (str_contains(strtolower($e->getMessage()), 'pin')) {
                $this->sendMessage($chatId, '❌ ' . $e->getMessage() . ' Try your PIN again, or use /cancel to stop.');
                return;
            }
            $this->clearState($chatId);
            $this->sendMessage($chatId, '❌ ' . $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('KayX bot gift card purchase failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            $this->clearState($chatId);
            $this->sendMessage($chatId, 'We could not submit the gift card request. No order was placed; please try again or contact support.');
        }
    }

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
                    "`/sell` — Sell a supported cryptocurrency (choose from the live list)\n" .
                    "  └ PIN required if set → choose coin → amount → proof (photo or txid) → payout\n" .
                    "`/buy` — Buy cryptocurrency\n" .
                    "  └ PIN required if set → choose coin → amount → wallet → proof (photo or txid)\n" .
                    "`/deposit` — Deposit NGN into your wallet balance\n" .
                    "  └ Pick account → enter amount → upload proof photo\n" .
                    "`/crypto_deposit` — Create a crypto deposit invoice and check its payment status\n" .
                    "`/usd_wallet` — Open your read-only crypto portfolio valuation\n" .
                    "`/deposit_status` — Refresh your latest crypto deposit status\n" .
                    "`/giftcards` — Choose to buy or sell a USA/UK gift card\n" .
                    "`/giftcard_status` — Check recent gift-card purchase status\n" .
                    "`/buygiftcard` — Buy with rate review and transaction PIN confirmation\n" .
                    "`/sellgiftcard` — Submit a card for admin verification (NGN credit only after approval)\n" .
                    "`/features` — Wallets, withdrawals, swaps, bills, gift cards, portfolio and support\n" .
                    "`/tools` — Open the digital tools workspace\n" .
                    "`/withdraw` — Open the secure wallet withdrawal page\n" .
                    "`/rates` — View current KayXchange NGN buy & sell rates\n" .
                    "`/balance` — Check your account balance\n" .
                    "`/transactions` or `/trades` — View recent deposits, withdrawals, trades, swaps, bills and gift cards\n\n" .
                    "You can message KAI in English or Nigerian Pidgin.\n\n" .
                    "🔒 *Security*\n" .
                    "Transaction PIN is required before every buy or sell trade.\n" .
                    "Wrong PIN 5× = 15-minute lockout. Set/change your PIN on the website.\n\n" .
                    "👤 *Account Commands*\n" .
                    "`/start` — Main menu (personalised when linked)\n" .
                    "`/verify` or `/status` — Show your account & verification status\n" .
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
     * Handle /rates command - show KayXchange platform buy/sell rates only
     */
    private function handleRatesCommand($chatId)
    {
        try {
            $rates = CryptoRate::query()->orderBy('coin')->get();

            if ($rates->isEmpty()) {
                $this->sendMessage($chatId, "⚠️ Rates are currently unavailable. Please try again shortly.");
                return;
            }

            $message = "📊 KayXchange platform rates\nNGN per $1 USD · Buy from us / Sell to us\n\n";

            foreach ($rates as $rate) {
                $coin = strtoupper($rate->coin);
                $buy  = number_format($rate->buy_rate);
                $sell = number_format($rate->sell_rate);
                $message .= "🪙 {$coin}\n  Buy: ₦{$buy} | Sell: ₦{$sell}\n\n";
            }

            $message .= "Updated: " . now()->timezone('Africa/Lagos')->format('d M Y, H:i') . " WAT\nRates can change before a transaction is confirmed.";

            $keyboard = [
                'inline_keyboard' => [[
                    $this->linkButton('💸 Buy Crypto', '/buy', 'cmd_buy'),
                    $this->linkButton('💵 Sell Crypto', '/sell', 'cmd_sell'),
                ]],
            ];

            $this->sendMessage($chatId, $message, null, $keyboard);
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

        // Send a QR code generated from the actual wallet address
        $barcodePath = $this->getBarcodePathForWallet($walletAddr);
        if ($barcodePath) {
            $this->sendPhoto($chatId, $barcodePath, "📷 Scan to get the {$coin} wallet address");
            @unlink($barcodePath);
        }
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

        // Send a QR code generated from the actual wallet address
        $barcodePath = $this->getBarcodePathForWallet($walletAddr);
        if ($barcodePath) {
            $this->sendPhoto($chatId, $barcodePath, "📷 Scan to get the USDT ({$network}) wallet address");
            @unlink($barcodePath);
        }
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
        $this->setState($chatId, 'sell_amount_review');

        $netLabel = $network && $network !== $coin ? " ({$network})" : '';
        $keyboard = ['inline_keyboard' => [
            [
                ['text' => '✅ Amount is Correct', 'callback_data' => 'sell_amount_action:correct'],
                ['text' => '✏️ Change Amount',     'callback_data' => 'sell_amount_action:change'],
            ],
            [['text' => '❌ Cancel', 'callback_data' => 'cancel']],
        ]];
        $this->sendMessage($chatId,
            "💰 *Trade Summary:*\n\n" .
            "🪙 Coin: *{$coin}{$netLabel}*\n" .
            "💵 You sell: *\${$amount}*\n" .
            "💴 You receive: *₦" . number_format($naira, 2) . "*\n" .
            "📈 Rate: ₦" . number_format($rate, 2) . "/USD\n\n" .
            "Is this amount correct?",
            'Markdown', $keyboard);
    }

    private function handleSellAmountAction(int $chatId, string $action): void
    {
        if ($action === 'change') {
            $data = $this->getData($chatId);
            $coin = $data['coin'] ?? '?';
            $rate = (float)($data['rate'] ?? 0);
            $this->setState($chatId, 'sell_amount');
            $this->sendMessage($chatId,
                "✏️ *Enter the new USD amount:*\n\n" .
                "💱 Rate: ₦" . number_format($rate, 2) . "/USD\n" .
                "_Minimum: \$1_",
                'Markdown');
            return;
        }

        // Amount is correct — proceed to proof method
        $data    = $this->getData($chatId);
        $coin    = $data['coin'] ?? '?';
        $network = $data['network'] ?? '';
        $amount  = $data['usd_amount'] ?? 0;
        $naira   = $data['naira_amount'] ?? 0;
        $this->setState($chatId, 'sell_proof_method');

        $netLabel = $network && $network !== $coin ? " ({$network})" : '';
        $keyboard = ['inline_keyboard' => [
            [
                ['text' => '📸 Upload Proof Photo',    'callback_data' => 'sell_proof_method:photo'],
                ['text' => '🔢 Enter Transaction ID', 'callback_data' => 'sell_proof_method:txid'],
            ],
        ]];
        $this->sendMessage($chatId,
            "✅ *Amount confirmed!*\n\n" .
            "💰 *{$coin}{$netLabel}*: \${$amount} → ₦" . number_format($naira, 2) . "\n\n" .
            "📋 *How would you like to provide proof of the crypto transfer?*",
            'Markdown', $keyboard);
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
            $rows[] = [['text' => "🏦 Saved Bank ({$user->bank_name} — {$user->account_number})", 'callback_data' => 'sell_payout:default_bank']];
        }
        $rows[] = [['text' => '🏦 External/Different Bank', 'callback_data' => 'sell_payout:external_bank']];
        $rows[] = [['text' => '💰 Wallet Balance (add to app balance)', 'callback_data' => 'sell_payout:wallet_balance']];
        $rows[] = [['text' => '❌ Cancel', 'callback_data' => 'cancel']];

        $keyboard = ['inline_keyboard' => $rows];
        $this->sendMessage($chatId,
            "✅ *Proof received!*\n\nSelect your payout method:",
            'Markdown', $keyboard);
    }

    private function handleSellProofMethodSelected(int $chatId, string $method): void
    {
        $this->mergeData($chatId, ['proof_method' => $method]);

        if ($method === 'photo') {
            $this->setState($chatId, 'sell_proof');

            // Generate a temporary signed upload token valid for 30 minutes
            $token     = Str::random(40);
            $expiresAt = now()->addMinutes(30);
            Cache::put("tg_upload_token_{$token}", [
                'chat_id'  => $chatId,
                'type'     => 'sell_proof',
                'expires'  => $expiresAt->timestamp,
            ], $expiresAt);

            $uploadUrl = rtrim(env('TELEGRAM_APP_URL', env('APP_URL', '')), '/') . '/tg/upload-proof?token=' . $token;

            $keyboard = ['inline_keyboard' => [
                [['text' => '🌐 Upload via Browser Link', 'url' => $uploadUrl]],
            ]];

            $this->sendMessage($chatId,
                "📸 *Upload Your Crypto Transfer Proof*\n\n" .
                "You can provide proof in two ways:\n\n" .
                "1️⃣ *Send the screenshot directly here* as a Telegram photo\n" .
                "2️⃣ *Tap the button below* to upload via your browser\n\n" .
                "_The upload link expires in 30 minutes._",
                'Markdown', $keyboard);

        } elseif ($method === 'txid') {
            $this->setState($chatId, 'sell_txid');
            $this->sendMessage($chatId,
                "🔢 *Enter Your Crypto Transaction Hash*\n\n" .
                "Please paste the transaction hash/ID from your blockchain wallet or exchange.\n\n" .
                "Example: `a1b2c3d4e5f6...`",
                'Markdown');
        }
    }

    private function handleBuyProofMethodSelected(int $chatId, string $method): void
    {
        $this->mergeData($chatId, ['proof_method' => $method]);

        if ($method === 'photo') {
            $this->setState($chatId, 'buy_proof');
            $this->sendMessage($chatId,
                "📸 *Upload your payment proof (screenshot/receipt as a photo)*\n" .
                "_Send the photo as a Telegram photo (not a file)_",
                'Markdown');
        } elseif ($method === 'txid') {
            $this->setState($chatId, 'buy_txid');
            $this->sendMessage($chatId,
                "🔢 *Enter your bank transfer reference/transaction ID*\n\n" .
                "Please provide the reference number from your bank transfer:",
                'Markdown');
        }
    }

    private function handleSellTxidInput(int $chatId, string $txid): void
    {
        if (empty(trim($txid))) {
            $this->sendMessage($chatId, "❌ Transaction ID cannot be empty. Please try again.");
            return;
        }

        $this->mergeData($chatId, ['txid' => trim($txid)]);
        $this->setState($chatId, 'sell_payout');

        $user = User::find($this->getData($chatId)['user_id'] ?? 0);
        $hasSavedBank = $user && !empty($user->bank_name) && $user->bank_name !== 'N/A';

        $rows = [];
        if ($hasSavedBank) {
            $rows[] = [['text' => "🏦 Saved Bank ({$user->bank_name} — {$user->account_number})", 'callback_data' => 'sell_payout:default_bank']];
        }
        $rows[] = [['text' => '🏦 External/Different Bank', 'callback_data' => 'sell_payout:external_bank']];
        $rows[] = [['text' => '💰 Wallet Balance (add to app balance)', 'callback_data' => 'sell_payout:wallet_balance']];
        $rows[] = [['text' => '❌ Cancel', 'callback_data' => 'cancel']];

        $keyboard = ['inline_keyboard' => $rows];
        $this->sendMessage($chatId,
            "✅ *Transaction ID received!*\n\nSelect your payout method:",
            'Markdown', $keyboard);
    }

    private function handleBuyTxidInput(int $chatId, string $txid): void
    {
        if (empty(trim($txid))) {
            $this->sendMessage($chatId, "❌ Transaction ID cannot be empty. Please try again.");
            return;
        }

        $this->mergeData($chatId, ['txid' => trim($txid)]);
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

    private function handleSellPayoutSelected(int $chatId, string $method): void
    {
        // External bank — collect details first (use Paystack bank list)
        if ($method === 'external_bank' && empty($this->getData($chatId)['ext_account_number'])) {
            $this->mergeData($chatId, ['payout_method' => 'external_bank']);

            // If user already selected bank + stored verified account, go straight to confirm
            if (!empty($this->getData($chatId)['ext_account_number'])) {
                $this->setState($chatId, 'sell_confirm');
                // call confirm renderer
                $this->handleSellPayoutSelected($chatId, 'external_bank');
                return;
            }

            // Load Paystack banks and show selection list
            $banks = $this->fetchPaystackBanks();
            if (empty($banks)) {
                // Fallback to legacy free text if Paystack banks are not available
                $this->setState($chatId, 'sell_ext_bank');
                $this->sendMessage($chatId,
                    "🏦 *Enter your bank details for payout (fallback):*\n\n" .
                    "Format: `Bank Name | Account Number | Account Name`\n\n" .
                    "Example:\n`GTBank | 0123456789 | John Doe`",
                    'Markdown');
                return;
            }

            $this->setState($chatId, null);
            $this->setState($chatId, 'sell_ext_bank_pick');

            $rows = [];
            foreach (array_slice($banks, 0, 30) as $bank) {
                $bankCode = (string)($bank['code'] ?? '');
                $bankName = (string)($bank['name'] ?? '');
                if ($bankCode === '' || $bankName === '') continue;

                // callback_data max length matters; keep it short
                $cbName = str_replace(['|', ':'], ['/', ' - '], $bankName);
                $rows[] = [[
                    'text' => $bankName,
                    'callback_data' => 'sell_ext_bank_select:' . $bankCode . '|' . $cbName,
                ]];
            }

            // Arrange into rows of 2
            $keyboardRows = [];
            $tmp = [];
            foreach ($rows as $row) {
                $tmp[] = $row[0];
                if (count($tmp) === 2) {
                    $keyboardRows[] = $tmp;
                    $tmp = [];
                }
            }
            if (!empty($tmp)) $keyboardRows[] = $tmp;

            $keyboardRows[] = [[['text' => '❌ Cancel', 'callback_data' => 'cancel']][0]];

            $this->sendMessage($chatId,
                "🏦 *Select your bank for payout*\n\n" .
                "_We use Paystack to validate the account._",
                'Markdown',
                ['inline_keyboard' => $keyboardRows]
            );
            return;
        }

        $this->mergeData($chatId, ['payout_method' => $method]);
        $data = $this->getData($chatId);
        if (empty($data['sender_wallet_step_done'])) {
            $this->setState($chatId, 'sell_sender_wallet');
            $this->sendMessage($chatId,
                "📬 *Your sending wallet (optional)*\n\n" .
                "Enter the wallet address you will send the crypto from. This helps our admin identify incoming transfers.\n\n" .
                "You can skip this if you do not know the address.",
                'Markdown',
                ['inline_keyboard' => [[['text' => 'Skip', 'callback_data' => 'sell_sender_wallet_skip']]]]
            );
            return;
        }
        $this->setState($chatId, 'sell_confirm');

        $data    = $this->getData($chatId);
        $user    = User::find($data['user_id'] ?? 0);
        $coin    = $data['coin'] ?? '?';
        $network = $data['network'] ?? '';
        $usd     = $data['usd_amount'] ?? 0;
        $naira   = $data['naira_amount'] ?? 0;

        $maskAccountNumber = function (?string $accNo): string {
            $accNo = (string) ($accNo ?? '');
            $digits = preg_replace('/\D/', '', $accNo);
            if ($digits === '' || strlen($digits) < 4) return '********';
            return '********' . substr($digits, -4);
        };

        $extBankName   = (string) ($data['ext_bank_name'] ?? '?');
        $extAccountNo  = (string) ($data['ext_account_number'] ?? '?');
        $extAccountName= (string) ($data['ext_account_name'] ?? '?');

        $payoutLabel = match($method) {
            'default_bank'   => "🏦 Bank: {$user->bank_name} — " . $maskAccountNumber($user->account_number) . " ({$user->account_name})",
            'wallet_balance' => '💰 Add to Wallet Balance',
            'external_bank'  => '🏦 ' . $extBankName . ' — ' . $maskAccountNumber($extAccountNo) . ' (' . $extAccountName . ')',
            default          => $method,
        };

        $netLabel = ($network && $network !== $coin) ? " ({$network})" : '';
        $msg = "📋 *Confirm Your Sell Trade*\n\n" .
               "🪙 Coin: *{$coin}{$netLabel}*\n" .
               "💵 Amount: *\$" . number_format($usd, 2) . "*\n" .
               "💴 Payout: *₦" . number_format($naira, 2) . "*\n" .
               "💳 Method: {$payoutLabel}\n\n" .
               "📬 Sending wallet: " . (($data['sender_wallet_address'] ?? '') ?: 'Not provided') . "\n\n" .
               "⚠️ _Make sure you have already sent the crypto before confirming._";

        $keyboard = ['inline_keyboard' => [
            [
                ['text' => '✅ Confirm Trade', 'callback_data' => 'sell_confirm'],
                ['text' => '❌ Cancel',         'callback_data' => 'cancel'],
            ],
        ]];

        $this->sendMessage($chatId, $msg, 'Markdown', $keyboard);
    }

    private function handleSellSenderWalletInput(int $chatId, string $address): void
    {
        $address = trim($address);
        if (strlen($address) > 255) {
            $this->sendMessage($chatId, 'That address is too long. Please re-enter it or tap Skip.');
            return;
        }
        $data = $this->getData($chatId);
        $method = (string) ($data['payout_method'] ?? '');
        if ($method === '') {
            $this->sendMessage($chatId, 'Sell session expired. Please start again with /sell.');
            $this->clearState($chatId);
            return;
        }
        $this->mergeData($chatId, ['sender_wallet_address' => $address ?: null, 'sender_wallet_step_done' => true]);
        $this->handleSellPayoutSelected($chatId, $method);
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
        $txid    = $data['txid']         ?? null;
        $method  = $data['payout_method'] ?? null;

        if (!$coin || !$usd || (!$proof && !$txid) || !$method) {
            $this->sendMessage($chatId, "❌ Missing trade data. Please start again with /sell");
            $this->clearState($chatId);
            return;
        }

        $walletKey  = ($coin === 'USDT' && $network) ? "USDT_{$network}" : $coin;
        $walletAddr = config("wallets.{$walletKey}", config("wallets.{$coin}", 'N/A'));

        // Resolve bank details based on payout method
        $bankName  = match($method) {
            'default_bank'   => ($user->bank_name ?? 'N/A'),
            'external_bank'  => ($data['ext_bank_name'] ?? 'N/A'),
            'wallet_balance' => 'WALLET BALANCE',
            default          => 'N/A',
        };
        $acctNumber = match($method) {
            'default_bank'  => ($user->account_number ?? 'N/A'),
            'external_bank' => ($data['ext_account_number'] ?? 'N/A'),
            default         => 'N/A',
        };
        $acctName = match($method) {
            'default_bank'   => ($user->account_name ?? 'N/A'),
            'external_bank'  => ($data['ext_account_name'] ?? 'N/A'),
            'wallet_balance' => ($user->name ?? 'N/A'),
            default          => 'N/A',
        };

        DB::beginTransaction();
        try {
            $trade = SellTrade::create([
                'user_id'         => $user->id,
                'name'            => $user->name,
                'coin'            => $coin,
                'network'         => $network,
                'usd_amount'      => $usd,
                'naira_amount'    => $naira,
                'rate_used'       => $data['rate'] ?? null,
                'proof'           => $proof,
                'transaction_ref' => $txid ?: 'SELL-TG-' . Str::upper(Str::random(8)),
                'payment_method'  => $method,
                'status'          => 'pending',
                'source'          => 'telegram_bot',
                'wallet_address'  => $walletAddr,
                'sender_wallet_address' => $data['sender_wallet_address'] ?? null,
                'bank_name'       => $bankName,
                'account_number'  => $acctNumber,
                'account_name'    => $acctName,
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

            // ── User in-app notification ──────────────────────────────────────
            try {
                \App\Models\Notification::create([
                    'user_id' => $user->id,
                    'type'    => 'sell_trade',
                    'title'   => '💰 Sell Trade Submitted',
                    'message' => "Your sell order for {$coin} (\${$usd}) has been submitted via Telegram. Ref: {$trade->transaction_ref}",
                    'data'    => json_encode(['reference' => $trade->transaction_ref, 'trade_id' => $trade->id]),
                    'read'    => false,
                ]);
            } catch (\Throwable $e) {
                Log::warning("Sell TG user notification failed: {$e->getMessage()}");
            }

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
                    'sender_wallet_address' => $trade->sender_wallet_address,
                    'payout_method' => $trade->payment_method,
                    'payout_destination' => $trade->payment_method === 'wallet_balance'
                        ? 'KayXchange Naira wallet'
                        : trim(($trade->bank_name ?? '') . ' ' . ($trade->account_number ?? '')),
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
        $this->setState($chatId, 'buy_proof_method');

        $data  = $this->getData($chatId);
        $coin  = $data['coin'] ?? '?';
        $naira = $data['naira_amount'] ?? 0;

        // Show company account for payment
        $companyAccount = $this->getCompanyBankDetails();

        $keyboard = ['inline_keyboard' => [
            [
                ['text' => '📸 Upload Proof Photo', 'callback_data' => 'buy_proof_method:photo'],
                ['text' => '🔢 Enter Transaction ID', 'callback_data' => 'buy_proof_method:txid'],
            ],
        ]];
        $msg = "📋 *Payment Instructions*\n\n" .
               "Transfer *₦" . number_format($naira, 2) . "* to:\n\n" .
               "🏦 *Bank:* {$companyAccount['bank']}\n" .
               "💳 *Account:* `{$companyAccount['number']}`\n" .
               "👤 *Name:* {$companyAccount['name']}\n\n" .
               "📋 *How would you like to provide proof?*";

        $this->sendMessage($chatId, $msg, 'Markdown', $keyboard);
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
        $txid    = $data['txid']             ?? null;
        $wallet  = $data['recipient_wallet'] ?? null;

        if (!$coin || !$usd || (!$proof && !$txid) || !$wallet) {
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
                'rate_used'        => $data['rate'] ?? null,
                'wallet_address'   => $wallet,
                'payment_proof'    => $proof,
                'payment_method'   => 'Bank Transfer',
                'status'           => 'pending',
                'source'           => 'telegram_bot',
                'transaction_ref'  => $txid ?: 'BUY-TG-' . Str::upper(Str::random(8)),
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

            // ── User in-app notification ──────────────────────────────────────
            try {
                \App\Models\Notification::create([
                    'user_id' => $user->id,
                    'type'    => 'buy_trade',
                    'title'   => '🛒 Buy Trade Submitted',
                    'message' => "Your buy order for {$coin} (\${$usd}) has been submitted via Telegram. Ref: {$trade->transaction_ref}",
                    'data'    => json_encode(['reference' => $trade->transaction_ref, 'trade_id' => $trade->id]),
                    'read'    => false,
                ]);
            } catch (\Throwable $e) {
                Log::warning("Buy TG user notification failed: {$e->getMessage()}");
            }

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
     * Handle /trades, /history, and /transactions - show recent account activity.
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

        $transactions = app(ChatTransactionHistoryService::class)->recent($user, 8);

        $message = "📋 Recent transactions and account activity\n\n";
        if ($transactions->isEmpty()) {
            $message .= "No transactions yet. Use /buy, /sell, /deposit or /withdraw to get started.\n\n";
        } else {
            foreach ($transactions as $transaction) {
                $icon = app(ChatTransactionHistoryService::class)->statusIcon($transaction['status']);
                $date = $transaction['date']?->timezone('Africa/Lagos')->format('d M, H:i') ?? 'Date unavailable';
                $message .= "{$icon} {$transaction['label']} — {$transaction['amount']}\n";
                $message .= "   Status: {$transaction['status']} · {$date}";
                if ($transaction['reference']) $message .= "\n   Ref: {$transaction['reference']}";
                $message .= "\n\n";
            }
        }

        $appUrl = $this->telegramAppUrl();
        $message .= "📜 Full transaction history: {$appUrl}/transactions/history";

        $keyboard = [
            'inline_keyboard' => [[
                $this->linkButton('📜 Full transaction history', '/transactions/history', 'cmd_transactions'),
            ]],
        ];

        $this->sendMessage($chatId, $message, null, $keyboard);
    }

    /**
     * Handle unknown commands
     */
    private function handleUnknownCommand($chatId)
    {
        $user = User::where('telegram_chat_id', $chatId)->first();

        if ($user) {
            $keyboard = [
                'inline_keyboard' => [
                    [
                        ['text' => '🤖 Ask AI', 'callback_data' => 'cmd_ai'],
                        ['text' => '📊 Rates',  'callback_data' => 'cmd_rates'],
                    ],
                    [
                        $this->linkButton('💸 Buy', '/buy', 'cmd_buy'),
                        $this->linkButton('💵 Sell', '/sell', 'cmd_sell'),
                    ],
                ],
            ];
            $this->sendMessage($chatId,
                "🤔 I couldn't quite understand that.\n\n" .
                "Want to ask KAI, the AI assistant? Tap 🤖 Ask AI below. You can chat in English or Nigerian Pidgin, for example: “Abeg, show my balance.”\n\n" .
                "• /buy — Buy cryptocurrency\n" .
                "• /sell — Sell cryptocurrency\n" .
                "• /rates — View platform rates\n" .
                "• /balance — Check your balances\n" .
                "• /transactions — View deposits, withdrawals, trades, swaps, bills and gift cards\n" .
                "• /help — See all commands",
                'Markdown',
                $keyboard
            );
        } else {
            $message = "🤔 I couldn't quite understand that.\n\n" .
                      "Try one of these:\n" .
                      "• Send me your email address to link your account\n" .
                      "• Type /help for available commands\n" .
                      "• You can use English or Nigerian Pidgin\n" .
                      "• Type /start to begin setup\n\n" .
                      "How can I help you today? 😊";
            $this->sendMessage($chatId, $message);
        }
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

        try {
            $transition = app(BalanceSettlementService::class)->updateSellStatus($tradeId, 'completed');
            if (!$transition['changed']) {
                $this->sendMessage($chatId, "⚠️ Trade #{$tradeId} has already been processed.");
                return;
            }
            $oldStatus = $transition['old_status'];
            $trade = $transition['trade'];

            if ($messageId) {
                $this->editMessage($chatId, $messageId,
                    "✅ *APPROVED* — Sell Trade #{$tradeId}\nRef: {$trade->transaction_ref}\n" .
                    "User: {$trade->name} | ₦" . number_format((float)$trade->naira_amount, 2));
            }

            // Notify the user
            $tradeUser = User::find($trade->user_id);
            if ($tradeUser && $tradeUser->telegram_chat_id) {
                $this->sendMessage((int)$tradeUser->telegram_chat_id,
                    "🎉 *Your Sell Trade is Approved!*\n\n" .
                    "🔖 Ref: `{$trade->transaction_ref}`\n" .
                    "💴 Amount: ₦" . number_format((float)$trade->naira_amount, 2) . "\n" .
                    "💳 Method: {$trade->payment_method}\n\n" .
                    ($trade->payment_method === 'wallet_balance'
                        ? "💰 ₦" . number_format((float)$trade->naira_amount, 2) . " has been credited to your wallet balance."
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
                    'payout_method' => $trade->payment_method,
                    'payout_destination' => $trade->payment_method === 'wallet_balance'
                        ? 'KayXchange Naira wallet'
                        : trim(($trade->bank_name ?? '') . ' ' . ($trade->account_number ?? '')),
                    'naira_amount' => number_format((float) ($trade->naira_amount ?? 0), 2),
                ]);
            } catch (\Throwable $e) {}

        } catch (\Throwable $e) {
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
        $transition = app(BalanceSettlementService::class)->updateSellStatus($tradeId, 'rejected');
        if (!$transition['changed']) {
            $this->sendMessage($chatId, "⚠️ Trade #{$tradeId} has already been processed.");
            return;
        }
        $trade = $transition['trade'];

        if ($messageId) {
            $this->editMessage($chatId, $messageId,
                "❌ *REJECTED* — Sell Trade #{$tradeId}\nRef: {$trade->transaction_ref}\n" .
                "User: {$trade->name} | ₦" . number_format((float)$trade->naira_amount, 2));
        }

        $tradeUser = User::find($trade->user_id);
        if ($tradeUser && $tradeUser->telegram_chat_id) {
            $this->sendMessage((int)$tradeUser->telegram_chat_id,
                "❌ *Your Sell Trade was Rejected*\n\n" .
                "🔖 Ref: `{$trade->transaction_ref}`\n" .
                "💴 Amount: ₦" . number_format((float)$trade->naira_amount, 2) . "\n\n" .
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
                "User: {$trade->name} | ₦" . number_format((float)$trade->naira_amount, 2));
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
                "User: {$trade->name} | ₦" . number_format((float)$trade->naira_amount, 2));
        }

        $tradeUser = User::find($trade->user_id);
        if ($tradeUser && $tradeUser->telegram_chat_id) {
            $this->sendMessage((int)$tradeUser->telegram_chat_id,
                "❌ *Your Buy Order was Rejected*\n\n" .
                "🔖 Ref: `{$trade->transaction_ref}`\n" .
                "💴 Amount: ₦" . number_format((float)$trade->naira_amount, 2) . "\n\n" .
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

        try {
            $changed = app(BalanceSettlementService::class)->approveWithdrawal($withdrawalId);
            if (!$changed) {
                $this->sendMessage($chatId, "⚠️ Withdrawal #{$withdrawalId} has already been processed.");
                return;
            }
            $withdrawal->refresh();
            $user->refresh();
        } catch (\Throwable $e) {
            Log::error("handleAdminApproveWithdrawal error: {$e->getMessage()}");
            $this->sendMessage($chatId, "❌ Failed to approve withdrawal: {$e->getMessage()}");
            return;
        }

        if ($messageId) {
            $bd = is_array($withdrawal->bank_account)
                ? $withdrawal->bank_account
                : (json_decode((string)$withdrawal->bank_account, true) ?? []);
            $this->editMessage($chatId, $messageId,
                "✅ *APPROVED* — Withdrawal #{$withdrawalId}\n" .
                "Ref: {$withdrawal->reference}\n" .
                "User: {$user->name} | ₦" . number_format((float)$withdrawal->amount, 2) .
                " + fee ₦" . number_format((float)($withdrawal->fee_amount ?? 0), 2) . "\n" .
                "Bank: " . ($bd['bank_name'] ?? 'N/A') . " · " . ($bd['account_number'] ?? 'N/A'));
        }

        if ($user->telegram_chat_id) {
            $this->sendMessage((int) $user->telegram_chat_id,
                "✅ *Withdrawal Approved!*\n\n" .
                "🔖 Ref: `{$withdrawal->reference}`\n" .
                "💴 Amount: ₦" . number_format((float)$withdrawal->amount, 2) . "\n" .
                "💸 Fee: ₦" . number_format((float)($withdrawal->fee_amount ?? 0), 2) . "\n\n" .
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

        $user = $withdrawal->user;
        $oldStatus = $withdrawal->status;
        $changed = app(BalanceSettlementService::class)->setWithdrawalStatus($withdrawalId, 'cancelled');
        if (!$changed) {
            $this->sendMessage($chatId, "⚠️ Withdrawal #{$withdrawalId} has already been processed.");
            return;
        }
        $withdrawal->refresh();

        if ($messageId) {
            $this->editMessage($chatId, $messageId,
                "❌ *REJECTED* — Withdrawal #{$withdrawalId}\n" .
                "Ref: {$withdrawal->reference}\n" .
                "User: " . ($user->name ?? 'N/A') . " | ₦" . number_format((float)$withdrawal->amount, 2));
        }

        if ($user && $user->telegram_chat_id) {
            $this->sendMessage((int) $user->telegram_chat_id,
                "❌ *Withdrawal Rejected*\n\n" .
                "🔖 Ref: `{$withdrawal->reference}`\n" .
                "💴 Amount: ₦" . number_format((float)$withdrawal->amount, 2) . "\n\n" .
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

    // ────────────────────────── AI Chat handlers ───────────────────────────────

    /**
     * Enter AI chat mode via /ai command.
     */
    private function handleAiChatCommand(int $chatId): void
    {
        $aiService = app(\App\Services\TelegramAiBotService::class);

        if (!$aiService->isEnabled()) {
            $this->sendMessage($chatId,
                "⚙️ The AI Trade Assistant is currently disabled. Check back later!");
            return;
        }

        $user = User::where('telegram_chat_id', $chatId)->first();

        if (!$user) {
            $this->sendMessage($chatId,
                "🔗 *Link Your Account First*\n\nSend your KayXchange email address to link your account before using the AI assistant.");
            return;
        }

        if (!$user->telegram_ai_enabled) {
            $this->sendMessage($chatId,
                "🤖 *AI Assistant Disabled*\n\n" .
                "You have disabled the AI Trade Assistant on your account.\n\n" .
                "To enable it, visit your Telegram settings on the KayXchange website.");
            return;
        }

        $this->setState($chatId, 'ai_chat');

        $welcomeMsg = trim(\App\Models\SiteContent::where('key', 'ai_bot_welcome_message')->value('value') ?? '');
        if (empty($welcomeMsg)) {
            $welcomeMsg = "🤖 *KAI — KayXchange AI Assistant*\n\nHello {$user->name}! I can help you with:\n" .
                          "• Current crypto rates\n" .
                          "• Buy/Sell guidance\n" .
                          "• Platform questions\n" .
                          "• Trade history & balance info\n\n" .
                          "Just type your question below. Type /cancel to exit AI mode.";
        }

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '🪙 Current Rates',   'callback_data' => 'cmd_rates'],
                    ['text' => '💰 My Balance',       'callback_data' => 'cmd_balance'],
                ],
                [
                    ['text' => '🎁 USA/UK Gift Cards', 'url' => url('/gift-cards')],
                    ['text' => '🎁 Gift Card Rates', 'url' => url('/gift-card-rates')],
                ],
                [
                    ['text' => '❌ Exit AI Mode',     'callback_data' => 'cancel'],
                ],
            ],
        ];

        $this->sendMessage($chatId, $welcomeMsg, 'Markdown', $keyboard);
    }

    /**
     * Handle text messages when the user is in ai_chat state.
     */
    private function handleAiChatInput(int $chatId, string $text): void
    {
        $user = User::where('telegram_chat_id', $chatId)->first();

        if (!$user) {
            $this->clearState($chatId);
            $this->sendMessage($chatId, "🔗 Please link your account first by sending your email address.");
            return;
        }

        // Show typing indicator
        try {
            Http::post("{$this->apiUrl}/sendChatAction", [
                'chat_id' => $chatId,
                'action'  => 'typing',
            ]);
        } catch (\Exception $e) {}

        $aiService = app(\App\Services\TelegramAiBotService::class);
        $reply     = $aiService->chat($chatId, $user, $text);

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '💸 Buy Crypto',    'callback_data' => 'cmd_buy'],
                    ['text' => '💵 Sell Crypto',   'callback_data' => 'cmd_sell'],
                ],
                [
                    ['text' => '🎁 USA/UK Gift Cards', 'url' => url('/gift-cards')],
                ],
                [
                    ['text' => '❌ Exit AI Mode',  'callback_data' => 'cancel'],
                ],
            ],
        ];

        $this->sendMessage($chatId, $reply, 'Markdown', $keyboard);
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
            /** @var User $user */
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

    // ─────────────────────────────── DEPOSIT FLOW ────────────────────────────────

    private function handleDepositCommand(int $chatId): void
    {
        $user = User::where('telegram_chat_id', $chatId)->first();
        if (!$user) {
            $this->sendMessage($chatId, "🔒 *Account not linked*\n\nSend your KayXchange email address or use /register to get started.", 'Markdown');
            return;
        }

        $accounts = \App\Models\CompanyAccount::where('is_active', true)->get();
        if ($accounts->isEmpty()) {
            $this->sendMessage($chatId, "❌ No bank accounts are currently available for deposit. Please contact support.");
            return;
        }

        $rows = $accounts->map(fn($acc) => [
            ['text' => "🏦 {$acc->bank_name} — {$acc->account_number}", 'callback_data' => "deposit_account:{$acc->id}"],
        ])->toArray();
        $rows[] = [['text' => '❌ Cancel', 'callback_data' => 'cancel']];

        $this->setState($chatId, 'deposit_select_account');
        $this->setData($chatId, ['user_id' => $user->id]);

        $this->sendMessage($chatId,
            "💳 *Deposit NGN to your wallet*\n\nSelect the bank account you'll transfer to:",
            'Markdown',
            ['inline_keyboard' => $rows]
        );
    }

    private function handleDepositAccountSelected(int $chatId, int $accountId): void
    {
        $account = \App\Models\CompanyAccount::find($accountId);
        if (!$account) {
            $this->sendMessage($chatId, "❌ Account not found. Please try /deposit again.");
            return;
        }

        $this->mergeData($chatId, ['account_id' => $accountId]);
        $this->setState($chatId, 'deposit_amount');

        $this->sendMessage($chatId,
            "🏦 *Bank Details*\n\n" .
            "Bank: *{$account->bank_name}*\n" .
            "Account Name: *{$account->account_name}*\n" .
            "Account Number: `{$account->account_number}`\n\n" .
            "💵 Please enter the *exact amount in NGN* you are depositing:\n_(e.g. 50000)_",
            'Markdown'
        );
    }

    private function handleDepositAmountInput(int $chatId, string $text): void
    {
        $amount = (float) preg_replace('/[^0-9.]/', '', $text);
        if ($amount < 100) {
            $this->sendMessage($chatId, "❌ Minimum deposit is ₦100. Please enter a valid amount:");
            return;
        }

        $this->mergeData($chatId, ['amount' => $amount]);
        $this->setState($chatId, 'deposit_proof');

        $this->sendMessage($chatId,
            "✅ Amount: *₦" . number_format($amount, 2) . "*\n\n" .
            "📸 Now send a *photo* of your payment receipt/teller as proof.\n" .
            "_Send it as a Telegram photo (not a file/document)._",
            'Markdown'
        );
    }

    private function handleDepositProofUpload(int $chatId, string $fileId): void
    {
        $data = $this->getData($chatId);
        $user = User::find($data['user_id'] ?? 0);

        if (!$user || empty($data['amount']) || empty($data['account_id'])) {
            $this->sendMessage($chatId, "❌ Session expired. Please start again with /deposit");
            $this->clearState($chatId);
            return;
        }

        $localPath = $this->downloadTelegramFile($fileId, 'payment_proofs');
        if (!$localPath) {
            $this->sendMessage($chatId, "❌ Could not save your proof photo. Please try again.");
            return;
        }

        DB::beginTransaction();
        try {
            $ref = 'DEP-TG-' . strtoupper(Str::random(8));
            $deposit = \App\Models\Deposit::create([
                'user_id'           => $user->id,
                'amount'            => $data['amount'],
                'currency'          => 'NGN',
                'status'            => 'pending',
                'company_account_id'=> $data['account_id'],
                'transaction_ref'   => $ref,
                'proof_of_payment'  => $localPath,
                'proof'             => $localPath,
                'payment_method'    => 'Bank Transfer (Telegram)',
            ]);

            DB::commit();
            $this->clearState($chatId);

            $this->sendMessage($chatId,
                "✅ *Deposit Submitted!*\n\n" .
                "🔖 Ref: `{$ref}`\n" .
                "💴 Amount: *₦" . number_format($data['amount'], 2) . "*\n" .
                "⏳ Status: *Pending Review*\n\n" .
                "Admin will credit your wallet once payment is confirmed. You'll be notified here.",
                'Markdown'
            );

            // Notify admin
            $adminMsg = "💰 *New Deposit via Telegram*\n\n" .
                "👤 User: *{$user->name}* ({$user->email})\n" .
                "💴 Amount: *₦" . number_format($data['amount'], 2) . "*\n" .
                "🔖 Ref: `{$ref}`\n" .
                "📋 Deposit ID: #{$deposit->id}\n\n" .
                "Tap below to approve or reject:";

            $keyboard = ['inline_keyboard' => [[
                ['text' => '✅ Approve', 'callback_data' => "approve_deposit:{$deposit->id}"],
                ['text' => '❌ Reject',  'callback_data' => "reject_deposit:{$deposit->id}"],
            ]]];

            $this->sendToAdminChats($adminMsg, $keyboard);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Telegram deposit creation failed', ['error' => $e->getMessage()]);
            $this->sendMessage($chatId, "❌ Could not submit your deposit. Please try again or contact support.");
        }
    }

    public function handleAdminApproveDeposit(int $chatId, int $depositId, ?int $messageId): void
    {
        $deposit = \App\Models\Deposit::with('user')->find($depositId);
        if (!$deposit) {
            $this->sendMessage($chatId, "❌ Deposit #{$depositId} not found.");
            return;
        }
        if ($deposit->status !== 'pending') {
            $this->sendMessage($chatId, "⚠️ Deposit #{$depositId} is already *{$deposit->status}*.", 'Markdown');
            return;
        }

        try {
            $approved = app(BalanceSettlementService::class)->approveDeposit($depositId);
            if (!$approved) {
                $this->sendMessage($chatId, "⚠️ Deposit #{$depositId} has already been processed.");
                return;
            }
            $deposit->refresh();
            $creditedAmount = max(0, (float) $deposit->amount - (float) ($deposit->fee_amount ?? 0));
            $user = $deposit->user;
            if ($user) {
                // In-app notification
                \App\Models\Notification::create([
                    'user_id' => $user->id,
                    'type'    => 'deposit_approved',
                    'title'   => 'Deposit Approved',
                    'message' => "Your deposit of ₦" . number_format($creditedAmount, 2) . " has been approved and added to your wallet.",
                ]);
                // Telegram notification to user
                if ($user->telegram_chat_id) {
                    $this->sendMessage((int) $user->telegram_chat_id,
                        "✅ *Deposit Approved!*\n\n" .
                        "₦" . number_format($creditedAmount, 2) . " has been added to your wallet balance.\n" .
                        "🔖 Ref: `{$deposit->transaction_ref}`",
                        'Markdown'
                    );
                }
            }
            $this->sendMessage($chatId, "✅ Deposit #{$depositId} approved. ₦" . number_format($creditedAmount, 2) . " credited to user.", 'Markdown');
        } catch (\Throwable $e) {
            $this->sendMessage($chatId, "❌ Failed to approve deposit: " . $e->getMessage());
        }
    }

    public function handleAdminRejectDeposit(int $chatId, int $depositId, ?int $messageId): void
    {
        $deposit = \App\Models\Deposit::with('user')->find($depositId);
        if (!$deposit) {
            $this->sendMessage($chatId, "❌ Deposit #{$depositId} not found.");
            return;
        }
        if ($deposit->status !== 'pending') {
            $this->sendMessage($chatId, "⚠️ Deposit #{$depositId} is already *{$deposit->status}*.", 'Markdown');
            return;
        }

        $rejected = app(BalanceSettlementService::class)->setDepositStatus($depositId, 'rejected');
        if (!$rejected) {
            $this->sendMessage($chatId, "⚠️ Deposit #{$depositId} has already been processed.");
            return;
        }
        $deposit->refresh();
        $user = $deposit->user;
        if ($user) {
            \App\Models\Notification::create([
                'user_id' => $user->id,
                'type'    => 'deposit_rejected',
                'title'   => 'Deposit Rejected',
                'message' => "Your deposit of ₦" . number_format($deposit->amount, 2) . " was rejected. Please contact support if this is an error.",
            ]);
            if ($user->telegram_chat_id) {
                $this->sendMessage((int) $user->telegram_chat_id,
                    "❌ *Deposit Rejected*\n\n" .
                    "Your deposit of ₦" . number_format($deposit->amount, 2) . " was not approved.\n" .
                    "Please contact support if you believe this is an error.",
                    'Markdown'
                );
            }
        }
        $this->sendMessage($chatId, "✅ Deposit #{$depositId} rejected.", 'Markdown');
    }

    // ─────────────────────── UPLOAD PROOF (for existing trades) ──────────────

    /**
     * Handle /upload command — lets users upload proof for any pending trade by ref.
     */
    private function handleUploadCommand(int $chatId): void
    {
        $user = User::where('telegram_chat_id', $chatId)->first();
        if (!$user) {
            $this->sendMessage($chatId,
                "🔒 *Account Not Linked*\n\nSend me your KayXchange email to link your account first.");
            return;
        }

        // Count pending trades that still need proof
        $needsBuyProof  = BuyTrade::where('user_id', $user->id)
            ->where('status', 'pending')->whereNull('payment_proof')->count();
        $needsSellProof = SellTrade::where('user_id', $user->id)
            ->where('status', 'pending')
            ->where(function ($q) { $q->whereNull('proof')->orWhere('proof', 'bot_initiated'); })
            ->count();

        if ($needsBuyProof + $needsSellProof === 0) {
            // Show latest pending trades for reference
            $this->sendMessage($chatId,
                "✅ *No Pending Proofs Needed*\n\n" .
                "All your pending trades already have proof uploaded.\n\n" .
                "Use /trades to view your trade history.");
            return;
        }

        $this->clearState($chatId);
        $this->setState($chatId, 'upload_ref');
        $this->setData($chatId, ['user_id' => $user->id]);

        $lines = "📋 *Trades needing proof:*\n";
        if ($needsBuyProof > 0) {
            $buys = BuyTrade::where('user_id', $user->id)->where('status', 'pending')
                ->whereNull('payment_proof')->latest()->take(3)->get();
            foreach ($buys as $b) {
                $lines .= "• BUY `{$b->transaction_ref}` — {$b->coin} \${$b->usd_amount}\n";
            }
        }
        if ($needsSellProof > 0) {
            $sells = SellTrade::where('user_id', $user->id)->where('status', 'pending')
                ->where(function ($q) { $q->whereNull('proof')->orWhere('proof', 'bot_initiated'); })
                ->latest()->take(3)->get();
            foreach ($sells as $s) {
                $lines .= "• SELL `{$s->transaction_ref}` — {$s->coin} \${$s->usd_amount}\n";
            }
        }

        $this->sendMessage($chatId,
            "📸 *Upload Payment Proof*\n\n{$lines}\n" .
            "Type your *trade reference number* (e.g. `BUY-BOT-XXXXXXXX`) and I'll ask for the photo.\n\n" .
            "_Type /cancel to stop._",
            'Markdown');
    }

    /**
     * Handle trade reference input in upload_ref state.
     */
    private function handleUploadRefInput(int $chatId, string $text): void
    {
        $data = $this->getData($chatId);
        $user = User::find($data['user_id'] ?? 0);

        if (!$user) {
            $this->sendMessage($chatId, "❌ Session expired. Please use /upload again.");
            $this->clearState($chatId);
            return;
        }

        $ref = trim(strtoupper($text));

        // Search buy trades
        $buyTrade = BuyTrade::where('transaction_ref', $ref)
            ->where('user_id', $user->id)->first();
        if ($buyTrade) {
            if ($buyTrade->status !== 'pending') {
                $this->sendMessage($chatId,
                    "❌ Trade `{$ref}` is *{$buyTrade->status}* — only pending trades can have proof uploaded.\n\n_Type /cancel to stop._",
                    'Markdown');
                return;
            }
            $this->mergeData($chatId, [
                'trade_type' => 'buy',
                'trade_id'   => $buyTrade->id,
                'trade_ref'  => $ref,
            ]);
            $this->setState($chatId, 'upload_proof');
            $hasProof = !empty($buyTrade->payment_proof);
            $note = $hasProof ? "\n_Existing proof will be replaced._" : '';
            $this->sendMessage($chatId,
                "✅ Found: *Buy {$buyTrade->coin}* — \$" . number_format($buyTrade->usd_amount, 2) . " (₦" . number_format($buyTrade->naira_amount, 2) . ")\n" .
                "Ref: `{$ref}`{$note}\n\n" .
                "📸 Send your *payment screenshot as a photo* now.\n_(Not a file — use Telegram's Photo option)_",
                'Markdown');
            return;
        }

        // Search sell trades
        $sellTrade = SellTrade::where('transaction_ref', $ref)
            ->where('user_id', $user->id)->first();
        if ($sellTrade) {
            if ($sellTrade->status !== 'pending') {
                $this->sendMessage($chatId,
                    "❌ Trade `{$ref}` is *{$sellTrade->status}* — only pending trades can have proof uploaded.\n\n_Type /cancel to stop._",
                    'Markdown');
                return;
            }
            $this->mergeData($chatId, [
                'trade_type' => 'sell',
                'trade_id'   => $sellTrade->id,
                'trade_ref'  => $ref,
            ]);
            $this->setState($chatId, 'upload_proof');
            $this->sendMessage($chatId,
                "✅ Found: *Sell {$sellTrade->coin}* — \$" . number_format((float)$sellTrade->usd_amount, 2) . " (₦" . number_format((float)$sellTrade->naira_amount, 2) . ")\n" .
                "Ref: `{$ref}`\n\n" .
                "📸 Send your *crypto send screenshot as a photo* now.\n_(Not a file — use Telegram's Photo option)_",
                'Markdown');
            return;
        }

        $this->sendMessage($chatId,
            "❌ No trade found with reference `{$ref}`.\n\nPlease double-check and try again, or type /cancel to stop.",
            'Markdown');
    }

    /**
     * Handle photo upload in upload_proof state.
     */
    private function handleUploadProofSubmit(int $chatId, string $fileId): void
    {
        $data = $this->getData($chatId);
        $user = User::find($data['user_id'] ?? 0);

        if (!$user || empty($data['trade_type']) || empty($data['trade_id'])) {
            $this->sendMessage($chatId, "❌ Session expired. Please use /upload again.");
            $this->clearState($chatId);
            return;
        }

        $localPath = $this->downloadTelegramFile($fileId, 'payment_proofs');
        if (!$localPath) {
            $this->sendMessage($chatId, "❌ Could not save your photo. Please try again.");
            return;
        }

        $tradeType = $data['trade_type'];
        $tradeId   = (int) $data['trade_id'];
        $ref       = $data['trade_ref'] ?? '?';

        if ($tradeType === 'buy') {
            $trade = BuyTrade::where('id', $tradeId)->where('user_id', $user->id)->first();
            if (!$trade) {
                $this->sendMessage($chatId, "❌ Trade not found. Please use /upload again.");
                $this->clearState($chatId);
                return;
            }
            $trade->update(['payment_proof' => $localPath]);
            $adminMsg = "📸 *Buy Proof Uploaded via Telegram*\n\n" .
                "👤 {$user->name} ({$user->email})\n" .
                "🔖 Ref: `{$ref}`\n" .
                "🪙 {$trade->coin}: \$" . number_format((float)$trade->usd_amount, 2) . "\n" .
                "💴 ₦" . number_format((float)$trade->naira_amount, 2);
        } else {
            $trade = SellTrade::where('id', $tradeId)->where('user_id', $user->id)->first();
            if (!$trade) {
                $this->sendMessage($chatId, "❌ Trade not found. Please use /upload again.");
                $this->clearState($chatId);
                return;
            }
            $trade->update(['proof' => $localPath]);
            $adminMsg = "📸 *Sell Proof Uploaded via Telegram*\n\n" .
                "👤 {$user->name} ({$user->email})\n" .
                "🔖 Ref: `{$ref}`\n" .
                "🪙 {$trade->coin}: \$" . number_format((float)$trade->usd_amount, 2) . "\n" .
                "💴 ₦" . number_format((float)$trade->naira_amount, 2);
        }

        $this->clearState($chatId);
        $this->sendMessage($chatId,
            "✅ *Proof uploaded successfully!*\n\n" .
            "🔖 Ref: `{$ref}`\n\n" .
            "Our team will review your trade shortly. Usually within 15–30 minutes. 🚀",
            'Markdown');

        // Notify admins
        $this->sendToAdminChats($adminMsg);
    }

    private function handleSellExtBankInput(int $chatId, string $text): void
    {
        // Legacy free-text flow retained for backward compatibility.
        // Prefer the bank-list flow via the Paystack-backed selection UI.
        $parts = array_map('trim', explode('|', $text));
        if (count($parts) < 3 || strlen($parts[1]) < 9) {
            $this->sendMessage($chatId,
                "⚠️ *Invalid format.* Please use:\n\n" .
                "`Bank Name | Account Number | Account Name`\n\n" .
                "Example:\n`GTBank | 0123456789 | John Doe`\n\n" .
                "_Type /cancel to abort._",
                'Markdown');
            return;
        }

        [$bankName, $accountNumber, $accountName] = [$parts[0], $parts[1], $parts[2]];

        // Validate with Paystack
        $paystackKey = config('services.paystack.secret_key');
        $bankCode    = $this->lookupNgBankCode($bankName);

        $shouldBlock = false;

        if ($paystackKey && $bankCode) {
            try {
                $resp = Http::withToken($paystackKey)
                    ->withOptions(['verify' => $this->resolveSSL(), 'timeout' => 12])
                    ->get('https://api.paystack.co/bank/resolve', [
                        'account_number' => $accountNumber,
                        'bank_code'      => $bankCode,
                    ]);

                $status = $resp->json('status');
                if ($resp->successful() && $status === true) {
                    $accountName = $resp->json('data.account_name');
                } else {
                    if ($status === false || $resp->status() === 422) {
                        $errMsg = $resp->json('message') ?? 'Account not found';
                        $this->sendMessage($chatId,
                            "❌ *Could not verify account:* {$errMsg}\n\n" .
                            "Please double-check your bank name and account number, then try again:\n\n" .
                            "`Bank Name | Account Number | Account Name`",
                            'Markdown');
                        return;
                    }
                    $shouldBlock = true;
                }
            } catch (\Throwable $e) {
                Log::warning("Paystack bank verify failed in Telegram sell: " . $e->getMessage());
                $shouldBlock = true;
            }
        } elseif ($paystackKey && !$bankCode) {
            $this->sendMessage($chatId,
                "⚠️ We couldn't match that bank name.\n\n" .
                "Please use the exact bank name (e.g. GTBank, Zenith Bank, Access Bank) and try again:\n\n" .
                "`Bank Name | Account Number | Account Name`",
                'Markdown'
            );
            return;
        }

        if ($shouldBlock) {
            $this->sendMessage($chatId,
                "⚠️ Bank verification is temporarily unavailable.\n\n" .
                "Please try again in a moment (or keep the same details and resend them).\n\n" .
                "`Bank Name | Account Number | Account Name`",
                'Markdown'
            );
            return;
        }

        // Store external bank details and jump to confirm
        $this->mergeData($chatId, [
            'ext_bank_name'       => $bankName,
            'ext_bank_code'       => $bankCode,
            'ext_account_number' => $accountNumber,
            'ext_account_name'   => $accountName,
        ]);

        $this->handleSellPayoutSelected($chatId, 'external_bank');
    }

    private function handleSellExternalBankSelected(int $chatId, string $bankCode, string $bankName): void
    {
        $bankCode = trim($bankCode);
        $bankName = trim($bankName);

        if ($bankCode === '' || $bankName === '') {
            $this->sendMessage($chatId, "⚠️ Invalid bank selection. Please try again.");
            return;
        }

        $this->mergeData($chatId, [
            'ext_bank_code' => $bankCode,
            'ext_bank_name' => $bankName,
        ]);

        $this->setState($chatId, 'sell_ext_bank_account');
        $this->sendMessage($chatId,
            "🏦 *{$bankName}* selected.\n\n" .
            "Enter the *account number* to receive payout (digits only).\n\n" .
            "_Example: 0123456789_\n\n" .
            "_Type /cancel to abort._",
            'Markdown'
        );
    }

    private function handleSellExtBankAccountInput(int $chatId, string $accountNumber): void
    {
        $accountNumber = trim($accountNumber);

        if ($accountNumber === '' || strlen(preg_replace('/\D/', '', $accountNumber)) < 9) {
            $this->sendMessage($chatId,
                "⚠️ Invalid account number. Please enter digits only.\n\n" .
                "_Example: 0123456789_",
                'Markdown'
            );
            return;
        }

        $data = $this->getData($chatId);
        $bankCode = (string)($data['ext_bank_code'] ?? '');
        $bankName = (string)($data['ext_bank_name'] ?? '');

        if ($bankCode === '') {
            $this->sendMessage($chatId, "⚠️ Bank not selected. Please choose your bank again.");
            $this->handleSellPayoutSelected($chatId, 'external_bank');
            return;
        }

        $paystackKey = config('services.paystack.secret_key');
        if (!$paystackKey) {
            $this->sendMessage($chatId,
                "⚠️ Paystack validation is not configured. Please try again later.",
                'Markdown'
            );
            return;
        }

        try {
            $resp = Http::withToken($paystackKey)
                ->withOptions(['verify' => $this->resolveSSL(), 'timeout' => 12])
                ->get('https://api.paystack.co/bank/resolve', [
                    'account_number' => $accountNumber,
                    'bank_code'      => $bankCode,
                ]);

            if ($resp->successful() && $resp->json('status') === true) {
                $verifiedAccountName = $resp->json('data.account_name');
            } else {
                $errMsg = $resp->json('message') ?? 'Account not found';
                $this->sendMessage($chatId,
                    "❌ *Could not verify account:* {$errMsg}\n\n" .
                    "Please re-enter the account number (or go back and pick another bank).",
                    'Markdown'
                );
                return;
            }
        } catch (\Throwable $e) {
            Log::warning("Paystack bank resolve failed in Telegram sell: " . $e->getMessage());
            $this->sendMessage($chatId,
                "⚠️ Bank verification is temporarily unavailable.\n\nPlease try again in a moment.",
                'Markdown'
            );
            return;
        }

        $this->mergeData($chatId, [
            'ext_account_number' => $accountNumber,
            'ext_account_name'   => $verifiedAccountName,
            'ext_bank_name'       => $bankName,
        ]);

        // Now show confirm screen (external_bank masking handled there)
        $this->setState($chatId, 'sell_confirm');
        $this->handleSellPayoutSelected($chatId, 'external_bank');
    }

    private function fetchPaystackBanks(): array
    {
        $paystackKey = config('services.paystack.secret_key');
        if (!$paystackKey) return [];

        try {
            $cacheKey = 'paystack_banks';
            return Cache::remember($cacheKey, now()->addMinutes(60), function () use ($paystackKey) {
                $resp = Http::withToken($paystackKey)
                    ->withOptions(['verify' => $this->resolveSSL(), 'timeout' => 12])
                    ->get('https://api.paystack.co/bank', [
                        'perPage' => 50,
                    ]);

                if (!$resp->successful()) {
                    return [];
                }

                $data = $resp->json('data') ?? [];
                if (!is_array($data)) return [];

                // normalize to [{code,name},...]
                return array_values(array_map(function ($b) {
                    return [
                        'code' => (string)($b['code'] ?? ''),
                        'name' => (string)($b['name'] ?? ''),
                    ];
                }, $data));
            });
        } catch (\Throwable $e) {
            Log::warning("fetchPaystackBanks failed: " . $e->getMessage());
            return [];
        }
    }

    private function lookupNgBankCode(string $bankName): ?string
    {
        $map = [
            'access'         => '044', 'access bank'    => '044',
            'gtbank'         => '058', 'gtb'            => '058', 'guaranty trust' => '058',
            'zenith'         => '057', 'zenith bank'    => '057',
            'first bank'     => '011', 'firstbank'      => '011', 'fbn'            => '011',
            'uba'            => '033', 'united bank'    => '033',
            'fidelity'       => '070', 'fidelity bank'  => '070',
            'sterling'       => '232', 'sterling bank'  => '232',
            'union bank'     => '032', 'union'          => '032',
            'fcmb'           => '214',
            'stanbic'        => '221', 'stanbic ibtc'   => '221',
            'ecobank'        => '050',
            'heritage'       => '030', 'heritage bank'  => '030',
            'keystone'       => '082', 'keystone bank'  => '082',
            'polaris'        => '076', 'polaris bank'   => '076',
            'wema'           => '035', 'wema bank'      => '035', 'alat' => '035',
            'citibank'       => '023',
            'jaiz'           => '301', 'jaiz bank'      => '301',
            'opay'           => '999992',
            'palmpay'        => '999991',
            'kuda'           => '090267', 'kuda bank'   => '090267',
            'moniepoint'     => '090405',
            'vfd'            => '566',
        ];
        return $map[strtolower(trim($bankName))] ?? null;
    }

    private function resolveSSL(): bool|string
    {
        foreach (['curl.cainfo', 'openssl.cafile'] as $ini) {
            $path = ini_get($ini);
            if ($path && file_exists($path)) return $path;
        }
        return true;
    }
}
