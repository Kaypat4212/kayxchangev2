<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\CryptoRate;
use App\Models\SiteContent;
use App\Models\AiSupportTicket;

class TelegramAiBotService
{
    private string $apiKey;
    private string $apiUrl;
    private string $model;

    // Max messages to keep in history per user
    private const HISTORY_TTL_MINUTES = 60;
    private const MAX_HISTORY = 12;

    public function __construct()
    {
        $this->apiKey = config('services.groq.api_key', env('GROQ_API_KEY', ''));
        $this->apiUrl = config('services.groq.api_url', 'https://api.groq.com/openai/v1/chat/completions');
        $this->model  = $this->getSetting('ai_bot_model', config('services.groq.model', 'llama-3.3-70b-versatile'));
    }

    // ─────────────────────────────── Public API ──────────────────────────────

    /**
     * Check whether the AI bot feature is enabled globally.
     */
    public function isEnabled(): bool
    {
        return (bool) $this->getSetting('ai_bot_enabled', '1');
    }

    /**
     * Process a user message and return the AI reply.
     * Conversation history is maintained in the cache per chatId.
     */
    public function chat(int $chatId, User $user, string $userMessage): string
    {
        if (!$this->isEnabled()) {
            return "⚙️ The AI Trade Assistant is currently unavailable. Please try again later.";
        }

        if (empty($this->apiKey)) {
            Log::error('TelegramAiBotService: GROQ_API_KEY is not configured.');
            return "⚙️ AI assistant is not configured yet. Please contact support.";
        }

        // ── Escalation detection ──────────────────────────────────────────────
        if ($this->isEscalationRequest($userMessage)) {
            return $this->createSupportTicket($chatId, $user, $userMessage);
        }

        // Load and append the new user message to history
        $history = $this->getHistory($chatId);
        $history[] = ['role' => 'user', 'content' => $userMessage];

        // Build system prompt with live context
        $system = $this->buildSystemPrompt($user);

        // Trim history to keep within token budget
        $trimmedHistory = array_slice($history, -self::MAX_HISTORY);

        $messages = array_merge(
            [['role' => 'system', 'content' => $system]],
            $trimmedHistory
        );

        try {
            $temperature = (float) $this->getSetting('ai_bot_temperature', '0.6');
            $maxTokens   = (int)   $this->getSetting('ai_bot_max_tokens', '600');

            $response = Http::timeout(45)
                ->withToken($this->apiKey)
                ->post($this->apiUrl, [
                    'model'       => $this->model,
                    'temperature' => $temperature,
                    'max_tokens'  => $maxTokens,
                    'messages'    => $messages,
                ]);

            if (!$response->successful()) {
                Log::error('TelegramAiBotService: Groq API error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return "⚠️ I had trouble responding. Please try again in a moment.";
            }

            $data    = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? '';

            if (empty($content)) {
                return "⚠️ I received an empty response. Please try rephrasing your question.";
            }

            // Persist the assistant reply to history
            $history[] = ['role' => 'assistant', 'content' => $content];
            $this->saveHistory($chatId, array_slice($history, -self::MAX_HISTORY));

            return $content;
        } catch (\Exception $e) {
            Log::error('TelegramAiBotService: Exception calling Groq', [
                'error'   => $e->getMessage(),
                'chat_id' => $chatId,
            ]);
            return "⚠️ Connection issue. Please try again shortly.";
        }
    }

    /**
     * Clear the conversation history for a chat.
     */
    public function clearHistory(int $chatId): void
    {
        Cache::forget("ai_history_{$chatId}");
    }

    // ─────────────────────── Escalation / Support Ticket ─────────────────────

    private const ESCALATION_PHRASES = [
        'talk to human', 'speak to human', 'real person', 'talk to admin',
        'contact support', 'human agent', 'live agent', 'talk to someone',
        'escalate', 'not helpful', "can't help", 'cannot help',
        'speak to agent', 'speak to support',
    ];

    private function isEscalationRequest(string $msg): bool
    {
        $lower = strtolower($msg);
        foreach (self::ESCALATION_PHRASES as $phrase) {
            if (str_contains($lower, $phrase)) return true;
        }
        return false;
    }

    private function createSupportTicket(int $chatId, User $user, string $question): string
    {
        try {
            $context = collect($this->getHistory($chatId))
                ->map(fn($m) => "[{$m['role']}]: {$m['content']}")
                ->implode("\n");

            AiSupportTicket::create([
                'user_id'    => $user->id,
                'session_id' => 'tg_' . $chatId,
                'question'   => $question,
                'context'    => $context,
                'status'     => 'open',
            ]);

            // Notify admin via Telegram
            try {
                $token  = env('KAYXCHANGE_TELEGRAM_BOT_TOKEN', env('TELEGRAM_BOT_TOKEN', ''));
                $chatIdAdmin = env('TELEGRAM_CHAT_ID', env('TELEGRAM_OWNER_CHAT_ID', ''));
                if ($token && $chatIdAdmin) {
                    Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                        'chat_id'    => $chatIdAdmin,
                        'text'       => "*KAI Support Ticket (Telegram)*\n\nUser: {$user->name} (#{$user->id})\nEmail: {$user->email}\nQuestion:\n_{$question}_\n\nAdmin reply: " . url('/admin/kaybot/tickets'),
                        'parse_mode' => 'Markdown',
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning('TelegramAiBotService: support ticket admin notify failed: ' . $e->getMessage());
            }
        } catch (\Throwable $e) {
            Log::error('TelegramAiBotService: support ticket creation failed: ' . $e->getMessage());
        }

        return "👋 I've flagged your request for our support team!\n\nA human agent will follow up with you shortly. You can also reach us directly at:\n📬 @TradewithkayxchangeBOT\n\nType /cancel to exit AI mode.";
    }

    /**
     * Return recent conversation history summary (last N exchanges) for display.
     */
    public function getHistorySummary(int $chatId, int $limit = 5): array
    {
        $history = $this->getHistory($chatId);
        return array_slice($history, -($limit * 2)); // each exchange = user + assistant
    }

    // ─────────────────────────── System Prompt ───────────────────────────────

    private function buildSystemPrompt(User $user): string
    {
        $customPrompt = trim($this->getSetting('ai_bot_system_prompt', ''));

        // Live rates context
        $ratesText = $this->buildRatesContext();

        // User context
        $userContext = "User: {$user->name} | Balance: ₦" . number_format($user->balance, 2) .
                       " | KYC: " . ($user->kyc_verified ? 'Verified' : 'Pending');

        $base = $customPrompt ?: (
            "You are KAI, the KayXchange AI Trade Assistant. " .
            "KayXchange is a Nigerian cryptocurrency exchange platform where users can buy and sell Bitcoin (BTC), " .
            "USDT, Ethereum (ETH) and other cryptocurrencies for Naira (NGN). " .
            "Your job is to help users with trade advice, rate queries, platform guidance, and answering questions. " .
            "Be friendly, concise, and helpful. Use emojis sparingly for warmth. " .
            "Always remind users to use /buy or /sell to place actual trades. " .
            "Never invent rates — use only the rates provided below. " .
            "If you don't know something, say so clearly."
        );

        $canSuggestTrades = (bool) $this->getSetting('ai_bot_trade_suggestions', '1');
        $tradeSuggestionNote = $canSuggestTrades
            ? "\n- You may suggest when a rate looks favourable, but always direct users to /buy or /sell to execute."
            : "\n- Do not suggest specific trades. Only provide informational answers.";

        return $base
            . "\n\nCurrent platform rates:\n{$ratesText}"
            . "\n\nCurrent user context: {$userContext}"
            . $tradeSuggestionNote
            . "\n\nToday: " . now()->format('d M Y, H:i') . " WAT";
    }

    private function buildRatesContext(): string
    {
        try {
            $rates = CryptoRate::orderBy('coin')->get();
            if ($rates->isEmpty()) {
                return "Rates not available.";
            }
            $lines = [];
            foreach ($rates as $rate) {
                $line = "{$rate->coin}";
                if ($rate->sell_rate) {
                    $line .= " | Buy from us: ₦" . number_format($rate->sell_rate, 2);
                }
                if ($rate->buy_rate) {
                    $line .= " | Sell to us: ₦" . number_format($rate->buy_rate, 2);
                }
                $lines[] = $line;
            }
            return implode("\n", $lines);
        } catch (\Exception $e) {
            Log::warning('TelegramAiBotService: Could not load rates', ['error' => $e->getMessage()]);
            return "Rates temporarily unavailable.";
        }
    }

    // ─────────────────────── Config helpers ──────────────────────────────────

    private function getSetting(string $key, string $default = ''): string
    {
        try {
            $value = SiteContent::where('key', $key)->value('value');
            return $value !== null ? $value : $default;
        } catch (\Exception $e) {
            return $default;
        }
    }

    // ─────────────────────── History helpers ─────────────────────────────────

    private function getHistory(int $chatId): array
    {
        return Cache::get("ai_history_{$chatId}", []);
    }

    private function saveHistory(int $chatId, array $history): void
    {
        Cache::put("ai_history_{$chatId}", $history, now()->addMinutes(self::HISTORY_TTL_MINUTES));
    }
}
