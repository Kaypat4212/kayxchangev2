<?php

namespace App\Http\Controllers;

use App\Models\SellTrade;
use App\Models\BuyTrade;
use App\Models\Deposit;
use App\Models\CryptoDeposit;
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
        $this->model  = config('services.groq.model', 'openai/gpt-oss-120b');
        $this->apiUrl = config('services.groq.api_url', 'https://api.groq.com/openai/v1/chat/completions');
    }

    // POST /ai/dashboard-insight
    public function dashboardInsight(Request $request): JsonResponse
    {
        $user = $request->user();
        $firstName = trim(explode(' ', (string) ($user->name ?? 'there'))[0]) ?: 'there';

        try {
            $sells = SellTrade::where('user_id', $user->id)->latest()->take(50)->get();
            $buys = BuyTrade::where('user_id', $user->id)->latest()->take(50)->get();
            $deps = Deposit::where('user_id', $user->id)->where('status', 'approved')->latest()->take(20)->get();
            $cryptoDeps = CryptoDeposit::where('user_id', $user->id)->where('status', 'completed')->latest()->take(20)->get();
        } catch (\Throwable $e) {
            Log::warning('Dashboard insight activity lookup failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            return response()->json(['insight' => "Hi {$firstName}! Welcome to KayXchange. Your dashboard is ready whenever you are. Explore the latest crypto rates, and contact support if you need a hand getting started."]);
        }

        // Aggregate stats
        $topSellCoins = $sells->groupBy('coin')->map->count()->sortDesc()->keys()->take(3)->toArray();
        $topBuyCoins  = $buys->groupBy('coin')->map->count()->sortDesc()->keys()->take(3)->toArray();
        $dayDist      = $sells->groupBy(fn($t) => $t->created_at->format('l'))->map->count()->sortDesc()->toArray();

        if ($sells->isEmpty() && $buys->isEmpty() && $deps->isEmpty() && $cryptoDeps->isEmpty()) {
            return response()->json(['insight' => "Hi {$firstName}! Welcome to KayXchange. Your dashboard is ready whenever you are. Explore the latest crypto rates, and contact support if you need a hand getting started."]);
        }

        $context = [
            'first_name'           => $firstName,
            'total_sells'          => $sells->count(),
            'total_buys'           => $buys->count(),
            'total_deposited_ngn'  => round($deps->sum('amount'), 2),
            'completed_crypto_deposits' => $cryptoDeps->count(),
            'avg_sell_naira'       => round($sells->avg('naira_amount') ?? 0),
            'avg_buy_naira'        => round($buys->avg('naira_amount') ?? 0),
            'top_sell_coins'       => $topSellCoins,
            'top_buy_coins'        => $topBuyCoins,
            'busiest_trade_days'   => array_keys($dayDist),
            'recent_sell_statuses' => $sells->take(10)->pluck('status')->toArray(),
            'recent_buy_statuses'  => $buys->take(10)->pluck('status')->toArray(),
        ];

        $system = 'You are a warm, helpful dashboard companion for KayXchange, a Nigerian crypto exchange. '
            . 'Write 1–3 short friendly sentences based only on the supplied account activity. '
            . 'Use plain text only, no HTML. Mention the user by first name and one practical next step. '
            . 'Do not make investment recommendations, predict prices, or claim an account action happened unless shown in the data.';

        $prompt = "Generate a personalised dashboard insight for this user:\n\n" . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $result = $this->callGroq($system, $prompt, 400);
        $text = trim(strip_tags((string) ($result['text'] ?? '')));
        if ($text === '') {
            $text = $this->fallbackInsight($firstName, $buys, $sells, $deps, $cryptoDeps);
        }
        return response()->json(['insight' => $text]);
    }

    private function fallbackInsight(string $firstName, $buys, $sells, $deposits, $cryptoDeposits): string
    {
        $coinCounts = $buys->pluck('coin')->merge($sells->pluck('coin'))->filter()->countBy();
        $favorite = $coinCounts->sortDesc()->keys()->first();
        $activity = $buys->count() + $sells->count();

        if ($activity > 0) {
            $coinTip = $favorite ? " {$favorite} is the most frequent coin in your recent activity." : '';
            return "Hi {$firstName}, you have {$buys->count()} buy(s) and {$sells->count()} sell(s) in your recent history.{$coinTip} Check the current platform rate before your next trade.";
        }

        if ($deposits->isNotEmpty() || $cryptoDeposits->isNotEmpty()) {
            return "Hi {$firstName}, your account has recorded {$deposits->count()} approved cash deposit(s) and {$cryptoDeposits->count()} completed crypto deposit(s). Check your wallet for the latest balance, and reach out to support if you need help.";
        }

        return "Hi {$firstName}! Welcome to KayXchange. Explore the latest crypto rates, and contact support if you need a hand getting started.";
    }

    private function callGroq(string $system, string $prompt, int $maxTokens = 512): array
    {
        if (empty($this->apiKey)) {
            return ['error' => 'AI is not configured yet.'];
        }
        try {
            $response = Http::connectTimeout(3)->timeout(8)
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
            return ['text' => trim((string) $response->json('choices.0.message.content', ''))];
        } catch (\Throwable $e) {
            Log::error('UserAiController Groq exception', ['msg' => $e->getMessage()]);
            return ['error' => 'AI request failed: ' . $e->getMessage()];
        }
    }
}
