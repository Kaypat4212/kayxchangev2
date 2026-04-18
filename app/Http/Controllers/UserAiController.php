<?php

namespace App\Http\Controllers;

use App\Models\SellTrade;
use App\Models\BuyTrade;
use App\Models\Deposit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UserAiController extends Controller
{
    private string $apiUrl;
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('services.groq.api_key', '');
        $this->model  = config('services.groq.model', 'llama-3.3-70b-versatile');
        $this->apiUrl = config('services.groq.api_url', 'https://api.groq.com/openai/v1/chat/completions');
    }

    // POST /ai/dashboard-insight
    public function dashboardInsight(Request $request): JsonResponse
    {
        $user = $request->user();

        $sells  = SellTrade::where('user_id', $user->id)->latest()->take(50)->get();
        $buys   = BuyTrade::where('user_id', $user->id)->latest()->take(50)->get();
        $deps   = Deposit::where('user_id', $user->id)->where('status', 'approved')->latest()->take(20)->get();

        if ($sells->isEmpty() && $buys->isEmpty()) {
            return response()->json([
                'insight' => '<p class="kx-muted">Complete your first trade to unlock personalised AI insights!</p>',
            ]);
        }

        // Aggregate stats
        $topSellCoins = $sells->groupBy('coin')->map->count()->sortDesc()->keys()->take(3)->toArray();
        $topBuyCoins  = $buys->groupBy('coin')->map->count()->sortDesc()->keys()->take(3)->toArray();
        $dayDist      = $sells->groupBy(fn($t) => $t->created_at->format('l'))->map->count()->sortDesc()->toArray();

        $context = [
            'first_name'           => explode(' ', $user->name)[0],
            'total_sells'          => $sells->count(),
            'total_buys'           => $buys->count(),
            'total_deposited_ngn'  => $deps->sum('amount'),
            'avg_sell_naira'       => round($sells->avg('naira_amount') ?? 0),
            'avg_buy_naira'        => round($buys->avg('naira_amount') ?? 0),
            'top_sell_coins'       => $topSellCoins,
            'top_buy_coins'        => $topBuyCoins,
            'busiest_trade_days'   => array_keys($dayDist),
            'recent_sell_statuses' => $sells->take(10)->pluck('status')->toArray(),
            'recent_buy_statuses'  => $buys->take(10)->pluck('status')->toArray(),
        ];

        $system = 'You are a helpful personal finance assistant for KayXchange, a Nigerian crypto exchange. '
            . 'Write a short, friendly personalised insight paragraph (2–4 sentences) for the user based on their activity data. '
            . 'Use plain HTML: only <p>, <strong>, <span>. Be encouraging and helpful. Do NOT use bullet points. '
            . 'Mention their name, preferred coin(s), and give one actionable tip.';

        $prompt = 'Generate a personalised dashboard insight for this user:\n\n' . json_encode($context);

        $result = $this->callGroq($system, $prompt, 400);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 500);
        }
        return response()->json(['insight' => $result['text']]);
    }

    private function callGroq(string $system, string $prompt, int $maxTokens = 512): array
    {
        if (empty($this->apiKey)) {
            return ['error' => 'AI is not configured yet.'];
        }
        try {
            $response = Http::timeout(30)
                ->withToken($this->apiKey)
                ->post($this->apiUrl, [
                    'model'       => $this->model,
                    'temperature' => 0.6,
                    'max_tokens'  => $maxTokens,
                    'messages'    => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user',   'content' => $prompt],
                    ],
                ]);
            if ($response->failed()) {
                $msg = $response->json('error.message') ?? 'AI error (HTTP ' . $response->status() . ')';
                return ['error' => $msg];
            }
            return ['text' => trim($response->json('choices.0.message.content', ''))];
        } catch (\Throwable $e) {
            Log::error('UserAiController Groq exception', ['msg' => $e->getMessage()]);
            return ['error' => 'AI request failed: ' . $e->getMessage()];
        }
    }
}
