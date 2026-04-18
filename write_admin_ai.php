<?php
// AdminAiController
$c1 = <<<'PHP'
<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SellTrade;
use App\Models\BuyTrade;
use App\Models\Deposit;
use App\Models\Kyc;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdminAiController extends Controller
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

    // POST /admin/ai/trade-summary
    public function tradeSummary(Request $request): JsonResponse
    {
        $pending = SellTrade::where('status', 'pending')
            ->with('user:id,name,email')
            ->latest()
            ->take(30)
            ->get()
            ->map(fn($t) => [
                'id'      => $t->id,
                'user'    => $t->user->name ?? 'N/A',
                'coin'    => $t->coin,
                'network' => $t->network,
                'amount'  => $t->amount,
                'usd'     => $t->usd_amount,
                'naira'   => $t->naira_amount,
                'since'   => $t->created_at->diffForHumans(),
            ]);

        $buyPending = BuyTrade::where('status', 'pending')
            ->with('user:id,name,email')
            ->latest()
            ->take(20)
            ->get()
            ->map(fn($t) => [
                'id'    => $t->id,
                'user'  => $t->user->name ?? 'N/A',
                'coin'  => $t->coin,
                'usd'   => $t->usd_amount,
                'naira' => $t->naira_amount,
                'since' => $t->created_at->diffForHumans(),
            ]);

        $context = json_encode([
            'pending_sell_count' => $pending->count(),
            'pending_buy_count'  => $buyPending->count(),
            'sell_trades'        => $pending->toArray(),
            'buy_trades'         => $buyPending->toArray(),
            'total_sell_naira'   => $pending->sum('naira'),
            'total_buy_naira'    => $buyPending->sum('naira'),
            'generated_at'       => now()->format('M d, Y H:i'),
        ]);

        $system = 'You are a financial operations analyst for KayXchange, a Nigerian crypto exchange. '
            . 'Review the provided pending trade data and write a concise, structured HTML summary for the admin. '
            . 'Use <h4>, <ul>, <li>, <strong>, <span> tags. Keep it scannable. Flag anything urgent.';

        $prompt = "Summarize these pending trades for admin review and highlight any that need urgent attention:\n\n{$context}";

        $result = $this->callGroq($system, $prompt, 1500);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 500);
        }
        return response()->json(['summary' => $result['text']]);
    }

    // POST /admin/ai/spot-suspicious  {user_id}
    public function spotSuspicious(Request $request): JsonResponse
    {
        $request->validate(['user_id' => 'required|integer|exists:users,id']);
        $userId = $request->integer('user_id');
        $user   = User::findOrFail($userId);

        $recentSells = SellTrade::where('user_id', $userId)->latest()->take(20)->get();
        $allSells    = SellTrade::where('user_id', $userId)->get();

        $avgAmount  = $allSells->avg('naira_amount') ?? 0;
        $avgUsd     = $allSells->avg('usd_amount') ?? 0;
        $topCoins   = $allSells->groupBy('coin')->map->count()->sortDesc()->keys()->take(3)->toArray();

        $context = [
            'user'            => ['name' => $user->name, 'email' => $user->email, 'joined' => $user->created_at->format('M Y'), 'kyc_verified' => $user->kyc_verified],
            'lifetime_trades' => $allSells->count(),
            'avg_trade_naira' => round($avgAmount, 2),
            'avg_trade_usd'   => round($avgUsd, 2),
            'top_coins'       => $topCoins,
            'recent_20'       => $recentSells->map(fn($t) => [
                'amount_naira' => $t->naira_amount,
                'amount_usd'   => $t->usd_amount,
                'coin'         => $t->coin,
                'status'       => $t->status,
                'created_at'   => $t->created_at->format('Y-m-d H:i'),
            ])->toArray(),
        ];

        $system = 'You are a fraud detection analyst for a crypto exchange. Analyze trade patterns and flag suspicious behaviour. '
            . 'Respond in structured HTML using <h5>, <ul>, <li>, <strong>. '
            . 'Give a risk score (Low/Medium/High) and bullet-point reasoning. Be concise.';

        $prompt = 'Analyze this user\'s trade history for suspicious behaviour, unusual patterns, or fraud signals:' . "\n\n" . json_encode($context);

        $result = $this->callGroq($system, $prompt, 800);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 500);
        }
        return response()->json(['analysis' => $result['text'], 'user_name' => $user->name]);
    }

    // POST /admin/ai/report  {query}
    public function report(Request $request): JsonResponse
    {
        $request->validate(['query' => 'required|string|max:500']);
        $query = $request->input('query');

        // Build real stats to ground the AI
        $now    = now();
        $stats  = [
            'today_sell_count'   => SellTrade::whereDate('created_at', today())->count(),
            'today_sell_naira'   => SellTrade::whereDate('created_at', today())->sum('naira_amount'),
            'week_sell_count'    => SellTrade::whereBetween('created_at', [$now->copy()->subDays(7), $now])->count(),
            'week_sell_naira'    => SellTrade::whereBetween('created_at', [$now->copy()->subDays(7), $now])->sum('naira_amount'),
            'month_sell_count'   => SellTrade::whereMonth('created_at', $now->month)->count(),
            'month_sell_naira'   => SellTrade::whereMonth('created_at', $now->month)->sum('naira_amount'),
            'today_buy_naira'    => BuyTrade::whereDate('created_at', today())->sum('naira_amount'),
            'week_buy_naira'     => BuyTrade::whereBetween('created_at', [$now->copy()->subDays(7), $now])->sum('naira_amount'),
            'pending_sells'      => SellTrade::where('status','pending')->count(),
            'pending_buys'       => BuyTrade::where('status','pending')->count(),
            'total_deposits'     => Deposit::sum('amount'),
            'today_deposits'     => Deposit::whereDate('created_at', today())->sum('amount'),
            'total_users'        => User::count(),
            'kyc_pending'        => Kyc::where('status','pending')->count(),
            'kyc_approved'       => Kyc::where('status','approved')->count(),
            'top_coins_week'     => SellTrade::whereBetween('created_at', [$now->copy()->subDays(7), $now])
                                        ->select('coin', DB::raw('count(*) as cnt'))
                                        ->groupBy('coin')->orderByDesc('cnt')->take(5)
                                        ->pluck('cnt','coin')->toArray(),
            'generated_at'       => $now->format('M d, Y H:i'),
        ];

        $system = 'You are a business intelligence assistant for KayXchange (a Nigerian crypto exchange). '
            . 'Answer the admin\'s natural language query using the provided platform statistics. '
            . 'Format your response as clean HTML using <h4>, <ul>, <li>, <strong>, <table> where appropriate. '
            . 'Be precise, cite numbers from the data, and highlight key insights.';

        $prompt = "Admin query: \"{$query}\"\n\nPlatform statistics:\n" . json_encode($stats, JSON_PRETTY_PRINT);

        $result = $this->callGroq($system, $prompt, 1200);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 500);
        }
        return response()->json(['report' => $result['text']]);
    }

    // POST /admin/ai/kyc-analyze {kyc_id}
    public function kycAnalyze(Request $request): JsonResponse
    {
        $request->validate(['kyc_id' => 'required|integer|exists:kycs,id']);
        $kyc  = Kyc::with('user')->findOrFail($request->integer('kyc_id'));
        $user = $kyc->user;

        $tradeCount  = SellTrade::where('user_id', $user->id)->count() + BuyTrade::where('user_id', $user->id)->count();
        $depositSum  = Deposit::where('user_id', $user->id)->where('status','approved')->sum('amount');
        $accountInfo = [
            'name'        => $user->name,
            'email'       => $user->email,
            'joined'      => $user->created_at->format('M Y'),
            'bank_name'   => $user->bank_name,
            'kyc_status'  => $kyc->status,
            'submitted'   => $kyc->created_at->diffForHumans(),
            'trade_count' => $tradeCount,
            'total_deposited_ngn' => $depositSum,
        ];

        $system = 'You are a KYC compliance officer for a Nigerian crypto exchange. '
            . 'Based on the user account information provided, give a structured compliance assessment. '
            . 'Use <h5>, <ul>, <li>, <strong>. Provide: risk level (Low/Medium/High), observations, and a recommended action (Approve / Request More Info / Reject).';

        $prompt = 'Perform a KYC compliance assessment for this user account:' . "\n\n" . json_encode($accountInfo);

        $result = $this->callGroq($system, $prompt, 700);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 500);
        }
        return response()->json(['analysis' => $result['text'], 'user_name' => $user->name]);
    }

    private function callGroq(string $system, string $prompt, int $maxTokens = 1024): array
    {
        if (empty($this->apiKey)) {
            return ['error' => 'GROQ_API_KEY is not configured. Set it in Settings > Environment.'];
        }
        try {
            $response = Http::timeout(45)
                ->withToken($this->apiKey)
                ->post($this->apiUrl, [
                    'model'       => $this->model,
                    'temperature' => 0.4,
                    'max_tokens'  => $maxTokens,
                    'messages'    => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user',   'content' => $prompt],
                    ],
                ]);
            if ($response->failed()) {
                $msg = $response->json('error.message') ?? 'Groq API error (HTTP ' . $response->status() . ')';
                Log::error('AdminAiController Groq error', ['status' => $response->status()]);
                return ['error' => $msg];
            }
            return ['text' => trim($response->json('choices.0.message.content', ''))];
        } catch (\Throwable $e) {
            Log::error('AdminAiController Groq exception', ['msg' => $e->getMessage()]);
            return ['error' => 'AI request failed: ' . $e->getMessage()];
        }
    }
}
PHP;
file_put_contents('C:/xampp/htdocs/kayxchangev2/app/Http/Controllers/Admin/AdminAiController.php', $c1);
echo 'AdminAiController: ' . strlen($c1) . ' bytes OK';
