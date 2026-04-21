<?php

namespace App\Http\Controllers;

use App\Models\AdminSetting;
use App\Models\AiChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AiChatController extends Controller
{
    private const DEFAULT_SYSTEM_PROMPT = <<<PROMPT
You are KayBot, a friendly and knowledgeable crypto trading assistant for KayXchange â€” a Nigerian crypto exchange platform. 
You help users with:
- Understanding how to buy and sell crypto (BTC, ETH, USDT, USDC, etc.)
- Explaining trading concepts (wallets, rates, spreads, confirmation times)
- Guiding users through the platform: how deposits work, how to check rates, trade history
- Explaining the KayXchange fee structure and policies
- General crypto market education (never give financial advice or tell users to buy/sell)

Platform context:
- KayXchange supports trading NGN â†” USDT/BTC/ETH/other cryptos
- Users send crypto to displayed wallet addresses, and NGN is credited to their bank
- Rates update regularly; platform rates may differ from market rates
- Support is also available via Telegram bot

Rules:
- Always be friendly, concise, and helpful
- Never promise profits or give financial investment advice
- If you don't know something specific to the platform, say "Please contact support via Telegram for this"
- Reply in the language the user writes in (English or Pidgin English are most common)
- Keep responses short (max 4 sentences unless explaining a process)
PROMPT;

    // â”€â”€â”€ Provider config â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    private const PROVIDERS = [
        'openai' => [
            'url'        => 'https://api.openai.com/v1/chat/completions',
            'key_setting'=> 'openai_api_key',
            'model_setting' => 'openai_model',
            'default_model' => 'gpt-4o-mini',
        ],
        'groq' => [
            'url'        => 'https://api.groq.com/openai/v1/chat/completions',
            'key_setting'=> 'groq_api_key',
            'model_setting' => 'groq_model',
            'default_model' => 'llama-3.3-70b-versatile',
        ],
    ];

    public function chat(Request $request)
    {
        if (AdminSetting::get('ai_chatbot_enabled', '1') == '0') {
            return response()->json(['reply' => 'The AI assistant is currently offline. Please contact support.']);
        }

        $request->validate(['message' => 'required|string|max:1000']);

        $provider   = AdminSetting::get('ai_provider', 'groq');
        $provConfig = self::PROVIDERS[$provider] ?? self::PROVIDERS['groq'];

        // DB first, then .env fallback (e.g. groq_api_key → GROQ_API_KEY)
        $apiKey = AdminSetting::get($provConfig['key_setting'])
               ?: env(strtoupper($provConfig['key_setting']));

        // Fallback: if active provider has no key, try the other one
        if (! $apiKey) {
            $other = $provider === 'openai' ? 'groq' : 'openai';
            $fallbackConfig = self::PROVIDERS[$other];
            $apiKey = AdminSetting::get($fallbackConfig['key_setting'])
                   ?: env(strtoupper($fallbackConfig['key_setting']));
            if ($apiKey) {
                $provConfig = $fallbackConfig;
                $provider   = $other;
            } else {
                return response()->json(['reply' => 'AI assistant is not configured yet. Please contact support.']);
            }
        }

        $user      = auth()->user();
        $sessionId = $request->session()->get('ai_chat_session') ?? Str::random(32);
        $request->session()->put('ai_chat_session', $sessionId);

        AiChatMessage::create([
            'user_id'    => $user?->id,
            'session_id' => $sessionId,
            'role'       => 'user',
            'content'    => $request->message,
        ]);

        $history = AiChatMessage::where('session_id', $sessionId)
            ->latest()->limit(10)->get()
            ->reverse()
            ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])
            ->values()->all();

        $systemPrompt = AdminSetting::get('ai_system_prompt') ?: self::DEFAULT_SYSTEM_PROMPT;
        $model        = AdminSetting::get($provConfig['model_setting']) ?: $provConfig['default_model'];

        $messages = array_merge(
            [['role' => 'system', 'content' => $systemPrompt]],
            $history
        );

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type'  => 'application/json',
            ])->timeout(20)->post($provConfig['url'], [
                'model'       => $model,
                'messages'    => $messages,
                'max_tokens'  => 400,
                'temperature' => 0.7,
            ]);

            if (! $response->successful()) {
                \Log::warning('AiChat error', ['provider' => $provider, 'status' => $response->status(), 'body' => $response->body()]);
                return response()->json(['reply' => 'Sorry, I couldn\'t process that right now. Please try again in a moment.']);
            }

            $reply = $response->json('choices.0.message.content') ?? 'No response from AI.';

            AiChatMessage::create([
                'user_id'    => $user?->id,
                'session_id' => $sessionId,
                'role'       => 'assistant',
                'content'    => $reply,
            ]);

            return response()->json(['reply' => $reply, 'provider' => $provider]);

        } catch (\Exception $e) {
            \Log::error('AiChat exception: ' . $e->getMessage());
            return response()->json(['reply' => 'Connection error. Please try again.']);
        }
    }

    public function clearSession(Request $request)
    {
        $request->session()->forget('ai_chat_session');
        return response()->json(['ok' => true]);
    }
}

