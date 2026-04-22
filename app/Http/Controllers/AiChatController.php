<?php

namespace App\Http\Controllers;

use App\Models\AdminSetting;
use App\Models\AiChatMessage;
use App\Models\AiSupportTicket;
use App\Models\CryptoRate;
use App\Models\GiftCardRate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiChatController extends Controller
{
    private const PROVIDERS = [
        'openai' => [
            'url'           => 'https://api.openai.com/v1/chat/completions',
            'key_setting'   => 'openai_api_key',
            'model_setting' => 'openai_model',
            'default_model' => 'gpt-4o-mini',
        ],
        'groq' => [
            'url'           => 'https://api.groq.com/openai/v1/chat/completions',
            'key_setting'   => 'groq_api_key',
            'model_setting' => 'groq_model',
            'default_model' => 'llama-3.3-70b-versatile',
        ],
    ];

    private const SUPPORTED_COINS = ['BTC', 'ETH', 'USDT', 'USDC', 'SOL', 'BNB'];

    private const ESCALATION_PHRASES = [
        'talk to human', 'speak to human', 'real person', 'talk to admin',
        'contact support', 'human agent', 'live agent', 'talk to someone',
        'escalate', 'not helpful', "can't help", 'cannot help',
    ];

    public function chat(Request $request)
    {
        if (AdminSetting::get('ai_chatbot_enabled', '1') == '0') {
            return response()->json(['reply' => 'The AI assistant is currently offline. Please contact support via Telegram.']);
        }

        $request->validate(['message' => 'required|string|max:1000']);

        [$apiKey, $provConfig, $provider] = $this->resolveProvider();
        if (! $apiKey) {
            return response()->json(['reply' => 'AI assistant is not configured yet. Please contact support.']);
        }

        $user      = auth()->user();
        $sessionId = $request->session()->get('ai_chat_session') ?? Str::random(32);
        $request->session()->put('ai_chat_session', $sessionId);
        $message   = trim($request->message);

        // 1. Check for pending admin reply (graceful fallback if table not yet migrated)
        try {
            $pendingReply = $this->checkPendingAdminReply($sessionId, $user);
            if ($pendingReply) {
                $this->saveMessage($user?->id, $sessionId, 'user', $message);
                return response()->json(['reply' => $pendingReply, 'admin_reply' => true]);
            }
        } catch (\Exception $e) {
            Log::warning('KayBot: admin reply check skipped — ' . $e->getMessage());
        }

        // 2. Manual escalation request
        if ($this->isEscalationRequest($message)) {
            $this->saveMessage($user?->id, $sessionId, 'user', $message);
            try {
                $ticketReply = $user
                    ? $this->createSupportTicket($user, $sessionId, $message, $this->getRecentContext($sessionId))
                    : 'Please log in so our team can follow up. You can also reach us on Telegram: @TradewithkayxchangeBOT';
            } catch (\Exception $e) {
                Log::warning('KayBot: ticket creation failed — ' . $e->getMessage());
                $ticketReply = 'Your request has been noted. Please reach us on Telegram: @TradewithkayxchangeBOT';
            }
            $this->saveMessage($user?->id, $sessionId, 'assistant', $ticketReply);
            return response()->json(['reply' => $ticketReply, 'escalated' => true]);
        }

        // 3. Rates flow
        if ($request->session()->has('kaybot_rates') || $this->isRatesQuery($message)) {
            if (! $this->isTradeIntent($message) && ! $this->isEscalationRequest($message)) {
                $this->saveMessage($user?->id, $sessionId, 'user', $message);
                $ratesResult = $this->handleRatesFlow($request, $message);
                if ($ratesResult !== null) {
                    $this->saveMessage($user?->id, $sessionId, 'assistant', $ratesResult['reply']);
                    return response()->json($ratesResult);
                }
            } else {
                $request->session()->forget('kaybot_rates');
            }
        }

        // 4. Trade flow
        $tradeState = $request->session()->get('kaybot_trade', null);
        if ($tradeState || $this->isTradeIntent($message)) {
            $this->saveMessage($user?->id, $sessionId, 'user', $message);
            $result = $this->handleTradeFlow($request, $user, $sessionId, $message, $tradeState);
            if ($result !== null) {
                $this->saveMessage($user?->id, $sessionId, 'assistant', $result['reply']);
                return response()->json($result);
            }
        }

        // 5. Normal AI response with optional live price injection
        $systemPrompt = $this->buildSystemPrompt($message);
        $this->saveMessage($user?->id, $sessionId, 'user', $message);

        $history = AiChatMessage::where('session_id', $sessionId)
            ->latest()->limit(12)->get()->reverse()
            ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])
            ->values()->all();

        $model    = AdminSetting::get($provConfig['model_setting']) ?: $provConfig['default_model'];
        $messages = array_merge([['role' => 'system', 'content' => $systemPrompt]], $history);

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type'  => 'application/json',
            ])->timeout(25)->post($provConfig['url'], [
                'model'       => $model,
                'messages'    => $messages,
                'max_tokens'  => 500,
                'temperature' => 0.7,
            ]);

            if (! $response->successful()) {
                Log::warning('AiChat error', ['status' => $response->status(), 'body' => $response->body()]);
                return response()->json(['reply' => "Sorry, I couldn't process that right now. Please try again."]);
            }

            $reply = $response->json('choices.0.message.content') ?? 'No response from AI.';

            if ($this->aiSignalsUncertainty($reply) && $user) {
                $this->createSupportTicket($user, $sessionId, $message, $this->getRecentContext($sessionId), $reply);
                $reply .= "\n\n_(I've also flagged this for our support team to follow up with you.)_";
            }

            $this->saveMessage($user?->id, $sessionId, 'assistant', $reply);
            return response()->json(['reply' => $reply]);

        } catch (\Exception $e) {
            Log::error('AiChat exception: ' . $e->getMessage());
            return response()->json(['reply' => 'Connection error. Please try again.']);
        }
    }

    // --- Trade Flow ---

    private function handleTradeFlow(Request $request, ?User $user, string $sessionId, string $message, ?array $state): ?array
    {
        if (! $state) {
            $lower = strtolower($message);
            if (preg_match('/\b(buy|purchase|get)\b/', $lower)) {
                $state = ['type' => 'buy', 'step' => 'coin'];
            } elseif (preg_match('/\b(sell|trade out|swap out)\b/', $lower)) {
                $state = ['type' => 'sell', 'step' => 'coin'];
            } else {
                return null;
            }
        }

        $type = $state['type'];
        $step = $state['step'];

        if (preg_match('/\b(cancel|stop|abort|never mind)\b/i', $message)) {
            $request->session()->forget('kaybot_trade');
            return ['reply' => "No problem! Trade cancelled. Anything else I can help you with? :)", 'trade_done' => true];
        }

        if ($step === 'coin') {
            $coin = $this->extractCoin($message);
            if ($coin) {
                $state = array_merge($state, ['coin' => $coin, 'step' => 'amount']);
                $request->session()->put('kaybot_trade', $state);
                $rate     = $this->getPlatformRate($coin, $type);
                $rateText = $rate ? "\nCurrent rate: **N" . number_format($rate) . " per \$1 USD**" : '';
                return ['reply' => "Great! You want to {$type} **{$coin}**.{$rateText}\n\nHow much? e.g. *50 USD*, *\$100*, or *N50,000*\n_(Type **cancel** to stop)_", 'trade_step' => 'amount'];
            }
            $request->session()->put('kaybot_trade', $state);
            $list = implode(', ', self::SUPPORTED_COINS);
            return ['reply' => "Which coin do you want to {$type}? We support: **{$list}**\n\nType the coin name e.g. *USDT* or *BTC*.", 'trade_step' => 'coin'];
        }

        if ($step === 'amount') {
            $coin           = $state['coin'];
            [$usd, $naira]  = $this->parseAmount($message, $coin, $type);
            if (! $usd) {
                return ['reply' => "I didn't catch that. Please tell me the amount:\n• *50 USD* or *\$50*\n• *N50,000*\n_(Type **cancel** to stop)_", 'trade_step' => 'amount'];
            }
            $state = array_merge($state, [
                'usd_amount'   => $usd,
                'naira_amount' => $naira,
                'step'         => 'amount_review',
            ]);
            $request->session()->put('kaybot_trade', $state);
            $summary = "**{$type} {$coin}**\nUSD: \${$usd}\nNGN: N" . number_format($naira, 2) . "\n\n";
            if ($type === 'buy') {
                // For buy, just ask to confirm the amount before wallet step
                return ['reply' => $summary . "Is this amount correct?\n• Type **yes** to continue\n• Type **change** to enter a different amount\n_(Type **cancel** to stop)_", 'trade_step' => 'amount_review'];
            }
            // For sell, also show bank option
            if ($user && $user->bank_name && $user->account_number) {
                return ['reply' => $summary . "Is this amount correct?\n• Type **yes** to use your saved bank (**{$user->bank_name}** - {$user->account_number})\n• Type **external** to use a different bank account\n• Type **change** to enter a different amount\n_(Type **cancel** to stop)_", 'trade_step' => 'amount_review'];
            }
            return ['reply' => $summary . "Is this amount correct?\n• Type **yes** to continue and enter your bank details\n• Type **change** to enter a different amount\n_(Type **cancel** to stop)_", 'trade_step' => 'amount_review'];
        }

        if ($step === 'amount_review') {
            $lower = strtolower(trim($message));
            if (str_contains($lower, 'change')) {
                $state = array_merge($state, ['step' => 'amount']);
                $request->session()->put('kaybot_trade', $state);
                $rate     = $this->getPlatformRate($state['coin'], $type);
                $rateText = $rate ? "\nCurrent rate: **N" . number_format($rate) . " per \$1 USD**" : '';
                return ['reply' => "No problem! Enter the new amount:{$rateText}\n\ne.g. *50 USD*, *\$100*, or *N50,000*\n_(Type **cancel** to stop)_", 'trade_step' => 'amount'];
            }
            if (! preg_match('/\byes\b|\bexternal\b/i', $lower)) {
                return ['reply' => "Type **yes** to continue" . ($type === 'sell' && $user && $user->bank_name ? ", **external** for a different bank," : "") . " or **change** to enter a different amount.", 'trade_step' => 'amount_review'];
            }
            // They said "yes" or "external"
            $useSaved = $type === 'sell' && $user && $user->bank_name && $user->account_number && ! str_contains($lower, 'external');
            if ($type === 'buy') {
                $state = array_merge($state, ['step' => 'wallet']);
                $request->session()->put('kaybot_trade', $state);
                return ['reply' => "Provide your **{$state['coin']} wallet address** to receive the crypto.\n_(Type **cancel** to stop)_", 'trade_step' => 'wallet'];
            }
            if ($useSaved) {
                $state = array_merge($state, [
                    'bank_name'      => $user->bank_name,
                    'account_number' => $user->account_number,
                    'account_name'   => $user->account_name ?? $user->name,
                    'step'           => 'confirm',
                ]);
                $request->session()->put('kaybot_trade', $state);
                $summary = "**sell {$state['coin']}**\nUSD: \${$state['usd_amount']}\nNGN: N" . number_format($state['naira_amount'], 2) . "\n\n";
                return ['reply' => $summary . "✅ I'll pay to your saved bank:\n**{$user->bank_name}** - {$user->account_number}\n\nType **confirm** to submit or **cancel** to stop.", 'trade_step' => 'confirm'];
            }
            // External bank — collect details
            $state = array_merge($state, ['step' => 'bank']);
            $request->session()->put('kaybot_trade', $state);
            return ['reply' => "Provide the bank details for NGN payout:\n\n*Bank Name | Account Number | Account Name*\n\nExample: *GTBank | 0123456789 | John Doe*\n_(Type **cancel** to stop)_", 'trade_step' => 'bank'];
        }

        if ($step === 'wallet') {
            if (strlen($message) < 10) {
                return ['reply' => "That wallet address looks too short. Please provide your full **{$state['coin']} wallet address**.", 'trade_step' => 'wallet'];
            }
            $companyWallet = config("wallets.{$state['coin']}", null);
            $walletInfo    = $companyWallet ? "\n\nSend payment to:\n`{$companyWallet}`\n_(Upload proof on trade page after confirming)_" : '';
            $state         = array_merge($state, ['wallet_address' => $message, 'step' => 'confirm']);
            $request->session()->put('kaybot_trade', $state);
            return ['reply' => "**Summary - Buy {$state['coin']}**\nUSD: \${$state['usd_amount']}\nNGN to pay: N" . number_format($state['naira_amount'], 2) . "\nYour wallet: `{$message}`{$walletInfo}\n\nType **confirm** to submit or **cancel** to stop.", 'trade_step' => 'confirm'];
        }

        if ($step === 'bank') {
            $parts = array_map('trim', explode('|', $message));
            if (count($parts) < 3 || strlen($parts[1]) < 9) {
                return ['reply' => "Please use this format:\n\n*Bank Name | Account Number | Account Name*\n\nExample: *GTBank | 0123456789 | John Doe*\n_(Type **cancel** to stop)_", 'trade_step' => 'bank'];
            }
            // Validate account via Paystack if secret key is available
            $paystackKey = config('services.paystack.secret_key');
            if ($paystackKey) {
                $bankCode = $this->lookupBankCode($parts[0]);
                if ($bankCode) {
                    try {
                        $resp = \Illuminate\Support\Facades\Http::withToken($paystackKey)
                            ->withOptions(['verify' => $this->sslVerify(), 'timeout' => 10])
                            ->get('https://api.paystack.co/bank/resolve', [
                                'account_number' => $parts[1],
                                'bank_code'      => $bankCode,
                            ]);
                        if ($resp->successful() && $resp->json('status') === true) {
                            $verified = $resp->json('data.account_name');
                            // Overwrite account_name with Paystack verified name
                            $parts[2] = $verified;
                        } elseif ($resp->status() === 422 || $resp->json('status') === false) {
                            return ['reply' => "❌ Could not verify account **{$parts[1]}** at **{$parts[0]}**. Please check the details and try again:\n\n*Bank Name | Account Number | Account Name*", 'trade_step' => 'bank'];
                        }
                    } catch (\Throwable $e) {
                        // Network issue — proceed without validation
                    }
                }
            }
            $state = array_merge($state, [
                'bank_name'      => $parts[0],
                'account_number' => $parts[1],
                'account_name'   => $parts[2],
                'step'           => 'confirm',
            ]);
            $request->session()->put('kaybot_trade', $state);
            return ['reply' => "**Summary - Sell {$state['coin']}**\nUSD: \${$state['usd_amount']}\nNGN payout: N" . number_format($state['naira_amount'], 2) . "\nBank: {$parts[0]} - {$parts[1]} ({$parts[2]})\n\nType **confirm** to submit or **cancel** to stop.", 'trade_step' => 'confirm'];
        }

        if ($step === 'confirm') {
            if (! preg_match('/\bconfirm\b/i', $message)) {
                return ['reply' => "Type **confirm** to submit your trade, or **cancel** to stop.", 'trade_step' => 'confirm'];
            }
            if (! $user) {
                $request->session()->forget('kaybot_trade');
                return ['reply' => "Please log in to submit a trade.", 'trade_done' => true];
            }
            $request->session()->forget('kaybot_trade');
            $result = $this->submitTrade($user, $state);
            if ($result['success']) {
                $ref = $result['ref'];
                if ($type === 'buy') {
                    try { $payUrl = route('buy.payment', $result['trade_id']); } catch (\Throwable $e) { $payUrl = url('/buy/payment/' . $result['trade_id']); }
                    $reply = "✅ Buy trade submitted!\n\nRef: `{$ref}`\nStatus: Pending\n\n**Next step — upload your payment proof:**\n[👉 Click here to upload proof]({$payUrl})\n\n_(Without proof your trade cannot be processed.)_";
                } else {
                    try { $payUrl = route('sell.payment', $result['trade_id']); } catch (\Throwable $e) { $payUrl = url('/sell/payment/' . $result['trade_id']); }
                    $companyWallet = config("wallets.{$state['coin']}", null);
                    $walletInfo = $companyWallet ? "\nSend **{$state['coin']}** to:\n`{$companyWallet}`\n\n" : "\n\n";
                    $reply = "✅ Sell trade submitted!\n\nRef: `{$ref}`\nStatus: Pending\n{$walletInfo}**Next step — upload your crypto send proof:**\n[👉 Click here to upload proof]({$payUrl})\n\n_(Upload a screenshot of your crypto transfer.)_";
                }
                return ['reply' => $reply, 'trade_done' => true, 'trade_ref' => $ref, 'payment_url' => $payUrl];
            }
            return ['reply' => "Could not submit: {$result['error']}\n\nPlease try via the website {$state['type']} page.", 'trade_done' => true];
        }

        return null;
    }

    private function submitTrade(User $user, array $state): array
    {
        try {
            $type = $state['type'];
            $ref  = strtoupper($type) . '-BOT-' . Str::upper(Str::random(8));
            if ($type === 'buy') {
                $trade = \App\Models\BuyTrade::create([
                    'user_id'          => $user->id,
                    'name'             => $user->name,
                    'coin'             => $state['coin'],
                    'usd_amount'       => $state['usd_amount'],
                    'naira_amount'     => $state['naira_amount'],
                    'wallet_address'   => $state['wallet_address'],
                    'network'          => $state['coin'],
                    'payment_method'   => 'Bank Transfer',
                    'status'           => 'pending',
                    'source'           => 'web_bot',
                    'ip_address'       => request()->ip(),
                    'transaction_ref'  => $ref,
                    'transaction_type' => 'buy',
                ]);
            } else {
                $trade = \App\Models\SellTrade::create([
                    'user_id'         => $user->id,
                    'name'            => $user->name,
                    'coin'            => $state['coin'],
                    'usd_amount'      => $state['usd_amount'],
                    'naira_amount'    => $state['naira_amount'],
                    'wallet_address'  => config("wallets.{$state['coin']}", ''),
                    'network'         => $state['coin'],
                    'payment_method'  => 'Bank Transfer',
                    'status'          => 'pending',
                    'source'          => 'web_bot',
                    'proof'           => null,
                    'bank_name'       => $state['bank_name'] ?? null,
                    'account_number'  => $state['account_number'] ?? null,
                    'account_name'    => $state['account_name'] ?? null,
                    'transaction_ref' => $ref,
                ]);
            }
            return ['success' => true, 'ref' => $ref, 'trade_id' => $trade->id];
        } catch (\Throwable $e) {
            Log::error('KayBot trade submit: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Server error. Please try via the website.'];
        }
    }

    // --- Support Tickets ---

    private function createSupportTicket(User $user, string $sessionId, string $question, string $context, ?string $aiReply = null): string
    {
        try {
            AiSupportTicket::create([
                'user_id'    => $user->id,
                'session_id' => $sessionId,
                'question'   => $question,
                'context'    => $context,
                'status'     => 'open',
            ]);
            $this->notifyAdminTelegram($user, $question);
        } catch (\Throwable $e) {
            Log::error('Support ticket creation failed: ' . $e->getMessage());
        }
        return "I've flagged your question for our support team!\n\nA human agent will reply shortly - you'll see their response here the next time you open KayBot.\n\nYou can also reach us on Telegram: @TradewithkayxchangeBOT";
    }

    private function checkPendingAdminReply(string $sessionId, ?User $user): ?string
    {
        if (! $user) return null;
        $ticket = AiSupportTicket::where('user_id', $user->id)
            ->where('session_id', $sessionId)
            ->where('status', 'answered')
            ->where('user_notified', false)
            ->latest()->first();
        if (! $ticket) return null;
        $ticket->update(['user_notified' => true]);
        return "**Reply from KayXchange Support:**\n\n{$ticket->admin_reply}\n\n_- KayXchange Team_";
    }

    private function notifyAdminTelegram(User $user, string $question): void
    {
        try {
            $token  = AdminSetting::get('telegram_token') ?: env('KAYXCHANGE_TELEGRAM_BOT_TOKEN');
            $chatId = AdminSetting::get('telegram_owner_chat_id') ?: env('TELEGRAM_CHAT_ID') ?: env('KAYXCHANGE_TELEGRAM_CHAT_ID');
            if (! $token || ! $chatId) return;
            Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id'    => $chatId,
                'text'       => "*KayBot Support Ticket*\n\nUser: {$user->name} (#{$user->id})\nEmail: {$user->email}\nQuestion:\n_{$question}_\n\nAdmin reply: " . url('/admin/kaybot/tickets'),
                'parse_mode' => 'Markdown',
            ]);
        } catch (\Throwable $e) {
            Log::warning('KayBot Telegram notify: ' . $e->getMessage());
        }
    }

    // --- Rates Flow ---

    private function isRatesQuery(string $msg): bool
    {
        if (str_contains($msg, '💹') || str_contains($msg, '🎁') || str_contains($msg, '📈')) return true;
        return (bool) preg_match('/\b(current\s+rates?|show\s+rates?|what\s+are\s+(the|your)\s+rates?|gift[\s\-]?card\s+rates?|crypto\s+rates?|exchange\s+rates?|rates?\s+today|coin\s+rates?|trading\s+rates?|see\s+rates?|your\s+rates?|ngn\s+rates?|naira\s+rates?|live\s+prices?|market\s+prices?)\b/i', $msg);
    }

    private function handleRatesFlow(Request $request, string $message): ?array
    {
        $lower = strtolower(trim($message));

        // Crypto rates — direct match (also handles "💹 Crypto rates" quick reply)
        if (preg_match('/\bcrypto\s*rates?\b/i', $lower) || str_contains($lower, '💹')) {
            $request->session()->forget('kaybot_rates');
            return ['reply' => $this->buildCryptoRatesResponse(), 'rates_type' => 'crypto'];
        }

        // Gift card rates — direct match (also handles "🎁 Gift card rates" quick reply)
        if (preg_match('/\bgift[\s\-]?card\s*rates?\b/i', $lower) || str_contains($lower, '🎁')) {
            $request->session()->forget('kaybot_rates');
            return ['reply' => $this->buildGiftCardRatesResponse(), 'rates_type' => 'giftcard'];
        }

        // Live prices — direct match (also handles "📈 Live crypto prices" quick reply)
        if (preg_match('/\blive\s*(crypto\s*)?prices?\b/i', $lower) || str_contains($lower, '📈')) {
            $request->session()->forget('kaybot_rates');
            return ['reply' => $this->buildLivePricesResponse(), 'rates_type' => 'live'];
        }

        // General "current rates" / "what are rates" — show choice prompt
        $request->session()->put('kaybot_rates', ['step' => 'choose']);
        return [
            'reply'         => "What rates would you like to see? 💰\n\nChoose an option:",
            'quick_replies' => ['💹 Crypto rates', '🎁 Gift card rates', '📈 Live crypto prices'],
        ];
    }

    private function buildCryptoRatesResponse(): string
    {
        $platformRates = CryptoRate::all()->keyBy(fn($r) => strtoupper($r->coin));
        if ($platformRates->isEmpty()) {
            return "Sorry, KayXchange crypto rates are not configured yet. Please contact support.";
        }

        // Fetch live USD prices from CoinGecko
        $livePrices = [];
        try {
            $r = Http::timeout(7)->get('https://api.coingecko.com/api/v3/simple/price', [
                'ids'           => 'bitcoin,ethereum,tether,usd-coin,solana,binancecoin',
                'vs_currencies' => 'usd',
            ]);
            if ($r->successful()) {
                $map = ['bitcoin' => 'BTC', 'ethereum' => 'ETH', 'tether' => 'USDT', 'usd-coin' => 'USDC', 'solana' => 'SOL', 'binancecoin' => 'BNB'];
                foreach ($map as $id => $sym) {
                    if (isset($r->json()[$id]['usd'])) $livePrices[$sym] = $r->json()[$id]['usd'];
                }
            }
        } catch (\Throwable) {}

        $lines = ["📊 **KayXchange Crypto Rates**\n_Rate = NGN per \\$1 USD_\n"];

        foreach ($platformRates as $coin => $rate) {
            $buyRate  = number_format($rate->buy_rate);
            $sellRate = number_format($rate->sell_rate);
            $line     = "**{$coin}**\n  Buy: ₦{$buyRate}/USD | Sell: ₦{$sellRate}/USD";

            if (isset($livePrices[$coin])) {
                $usd     = $livePrices[$coin];
                $ngnBuy  = number_format($usd * $rate->buy_rate, 0);
                $ngnSell = number_format($usd * $rate->sell_rate, 0);
                $usdFmt  = $usd >= 1000 ? '$' . number_format($usd, 0) : '$' . number_format($usd, 2);
                $line   .= "\n  Market: {$usdFmt} → ₦{$ngnBuy} (buy) / ₦{$ngnSell} (sell)";
            }
            $lines[] = $line;
        }

        $lines[] = "\n_Type **buy crypto** or **sell crypto** to start a trade._";
        return implode("\n\n", $lines);
    }

    private function buildGiftCardRatesResponse(): string
    {
        $url         = url('/gift-card-rates');
        $activeRates = GiftCardRate::where('is_active', true)
            ->orderBy('category')->orderBy('name')
            ->limit(10)->get();

        $msg  = "🎁 **Gift Card Rates**\n\n";
        $msg .= "View the full, up-to-date rates table:\n[🔗 {$url}]({$url})\n";

        if ($activeRates->isNotEmpty()) {
            $msg .= "\n**Sample rates:**\n";
            $currentCategory = null;
            foreach ($activeRates as $r) {
                if ($r->category !== $currentCategory) {
                    $currentCategory = $r->category;
                    $msg .= "\n_" . ucfirst($r->category ?? 'General') . "_\n";
                }
                $rateStr = $r->buy_rate ? '₦' . number_format($r->buy_rate) : '—';
                $msg    .= "• {$r->name} ({$r->country}/{$r->currency}): {$rateStr}/unit\n";
            }
        }

        $msg .= "\n_For the complete list visit the link above, or type **contact support** to speak to an agent._";
        return $msg;
    }

    private function buildLivePricesResponse(): string
    {
        $platformRates = CryptoRate::all()->keyBy(fn($r) => strtoupper($r->coin));

        try {
            $r = Http::timeout(8)->get('https://api.coingecko.com/api/v3/simple/price', [
                'ids'                 => 'bitcoin,ethereum,tether,usd-coin,solana,binancecoin',
                'vs_currencies'       => 'usd',
                'include_24hr_change' => 'true',
            ]);
            if (! $r->successful()) {
                return "Sorry, I couldn't fetch live prices right now. Please try again in a moment.";
            }

            $data  = $r->json();
            $map   = ['bitcoin' => 'BTC', 'ethereum' => 'ETH', 'tether' => 'USDT', 'usd-coin' => 'USDC', 'solana' => 'SOL', 'binancecoin' => 'BNB'];
            $lines = ["📈 **Live Crypto Prices**\n_USD from CoinGecko · NGN based on KayXchange rates_\n"];

            foreach ($map as $id => $sym) {
                if (! isset($data[$id]['usd'])) continue;
                $usd    = $data[$id]['usd'];
                $change = $data[$id]['usd_24h_change'] ?? null;
                $arrow  = $change === null ? '' : ($change >= 0 ? ' ▲' . number_format($change, 1) . '%' : ' ▼' . number_format(abs($change), 1) . '%');
                $usdFmt = $usd >= 1000 ? '$' . number_format($usd, 0) : '$' . number_format($usd, 2);
                $line   = "**{$sym}** {$usdFmt}{$arrow}";

                $rate = $platformRates[$sym] ?? null;
                if ($rate) {
                    $ngnBuy  = number_format($usd * $rate->buy_rate, 0);
                    $ngnSell = number_format($usd * $rate->sell_rate, 0);
                    $line   .= "\n  ₦{$ngnBuy} (buy) / ₦{$ngnSell} (sell)";
                }
                $lines[] = $line;
            }

            $lines[] = "\n_Type **buy crypto** or **sell crypto** to trade._";
            return implode("\n\n", $lines);
        } catch (\Throwable) {
            return "Sorry, I couldn't fetch live prices right now. Please try again in a moment.";
        }
    }

    // --- Live Prices & System Prompt ---

    private function buildSystemPrompt(string $message): string
    {
        $base = AdminSetting::get('ai_system_prompt') ?: $this->defaultSystemPrompt();
        if ($this->isPricingQuestion($message)) {
            $live     = $this->getLivePrices();
            $platform = $this->getPlatformRatesText();
            $base    .= "\n\n--- LIVE MARKET DATA ---\nCoinGecko prices (USD): {$live}\nKayXchange rates (NGN per USD): {$platform}\n---";
        }
        return $base;
    }

    private function getLivePrices(): string
    {
        try {
            $r = Http::timeout(6)->get('https://api.coingecko.com/api/v3/simple/price', [
                'ids'           => 'bitcoin,ethereum,tether,usd-coin,solana,binancecoin',
                'vs_currencies' => 'usd',
            ]);
            if (! $r->successful()) return 'unavailable';
            $data = $r->json();
            $map  = ['bitcoin' => 'BTC', 'ethereum' => 'ETH', 'tether' => 'USDT', 'usd-coin' => 'USDC', 'solana' => 'SOL', 'binancecoin' => 'BNB'];
            $lines = [];
            foreach ($map as $id => $sym) {
                if (isset($data[$id]['usd'])) {
                    $lines[] = "{$sym}: $" . number_format($data[$id]['usd'], 2);
                }
            }
            return implode(', ', $lines) ?: 'unavailable';
        } catch (\Throwable $e) {
            return 'unavailable';
        }
    }

    private function getPlatformRatesText(): string
    {
        $rates = CryptoRate::all();
        if ($rates->isEmpty()) return 'not set';
        return $rates->map(fn($r) => "{$r->coin}: buy N{$r->buy_rate} sell N{$r->sell_rate}")->implode(', ');
    }

    private function getPlatformRate(string $coin, string $type): ?float
    {
        $r = CryptoRate::where('coin', $coin)->first();
        return $r ? (float) ($type === 'buy' ? $r->buy_rate : $r->sell_rate) : null;
    }

    // --- Helpers ---

    private function resolveProvider(): array
    {
        $provider   = AdminSetting::get('ai_provider', 'groq');
        $provConfig = self::PROVIDERS[$provider] ?? self::PROVIDERS['groq'];
        $apiKey     = AdminSetting::get($provConfig['key_setting']) ?: env(strtoupper($provConfig['key_setting']));
        if (! $apiKey) {
            $other = $provider === 'openai' ? 'groq' : 'openai';
            $fb    = self::PROVIDERS[$other];
            $fbKey = AdminSetting::get($fb['key_setting']) ?: env(strtoupper($fb['key_setting']));
            if ($fbKey) return [$fbKey, $fb, $other];
            return [null, $provConfig, $provider];
        }
        return [$apiKey, $provConfig, $provider];
    }

    private function saveMessage(?int $userId, string $sessionId, string $role, string $content): void
    {
        AiChatMessage::create(['user_id' => $userId, 'session_id' => $sessionId, 'role' => $role, 'content' => $content]);
    }

    private function getRecentContext(string $sessionId): string
    {
        return AiChatMessage::where('session_id', $sessionId)->latest()->limit(6)->get()->reverse()
            ->map(fn($m) => "[{$m->role}]: {$m->content}")->implode("\n");
    }

    private function isTradeIntent(string $msg): bool
    {
        return (bool) preg_match('/\b(buy|sell|purchase|trade|swap)\s+(some\s+)?(btc|eth|usdt|usdc|sol|bnb|bitcoin|ethereum|crypto)\b/i', $msg)
            || (bool) preg_match('/\bwant to (buy|sell)\b/i', $msg)
            || (bool) preg_match('/\b(buy|sell) crypto\b/i', $msg);
    }

    private function isEscalationRequest(string $msg): bool
    {
        $lower = strtolower($msg);
        foreach (self::ESCALATION_PHRASES as $phrase) {
            if (str_contains($lower, $phrase)) return true;
        }
        return false;
    }

    private function isPricingQuestion(string $msg): bool
    {
        return (bool) preg_match('/\b(price|rate|cost|worth|value|how much|market|usd|ngn|naira|dollar)\b/i', $msg);
    }

    private function aiSignalsUncertainty(string $reply): bool
    {
        foreach (["I don't know", "I'm not sure", "I cannot provide", "not certain", "you may want to contact", "reach out to support", "I don't have information"] as $p) {
            if (stripos($reply, $p) !== false) return true;
        }
        return false;
    }

    private function extractCoin(string $msg): ?string
    {
        $map = ['bitcoin' => 'BTC', 'btc' => 'BTC', 'ethereum' => 'ETH', 'eth' => 'ETH', 'usdt' => 'USDT', 'tether' => 'USDT', 'usdc' => 'USDC', 'sol' => 'SOL', 'solana' => 'SOL', 'bnb' => 'BNB', 'binance' => 'BNB'];
        $lower = strtolower($msg);
        foreach ($map as $key => $sym) {
            if (str_contains($lower, $key)) return $sym;
        }
        return null;
    }

    private function parseAmount(string $msg, string $coin, string $type): array
    {
        preg_match('/[\$N]?\s*([0-9,]+(?:\.[0-9]+)?)/i', $msg, $m);
        if (! isset($m[1])) return [null, null];
        $amount = (float) str_replace(',', '', $m[1]);
        $isNgn  = (bool) preg_match('/N|ngn|naira/i', $msg);
        $rate   = $this->getPlatformRate($coin, $type) ?? 1600;
        return $isNgn ? [round($amount / $rate, 2), $amount] : [$amount, round($amount * $rate, 2)];
    }

    private function defaultSystemPrompt(): string
    {
        return "You are KayBot, a smart and friendly crypto trading assistant for KayXchange - a Nigerian crypto exchange.\n\nCapabilities:\n- Answer general crypto questions (Bitcoin, blockchain, wallets, DeFi, etc.)\n- Provide KayXchange platform info (rates, fees, how to trade, deposit, trade status)\n- Help users place buy/sell trades conversationally\n- Escalate to human support when needed\n\nPlatform:\n- KayXchange lets Nigerians trade NGN <-> BTC/ETH/USDT/USDC/SOL/BNB\n- Buy: Pay NGN via bank transfer -> receive crypto to wallet\n- Sell: Send crypto -> receive NGN to bank account\n- KYC required before trading\n- Support Telegram: @TradewithkayxchangeBOT\n\nRules:\n- Be friendly, concise (2-4 sentences unless explaining a process)\n- NEVER give financial investment advice or price predictions\n- If you can't answer, offer to escalate to support\n- Use English or Nigerian Pidgin based on how the user writes\n- Use live data provided in context for prices - never guess";
    }

    private function sslVerify(): bool|string
    {
        foreach (['curl.cainfo', 'openssl.cafile'] as $ini) {
            $path = ini_get($ini);
            if ($path && file_exists($path)) return $path;
        }
        return true;
    }

    private function lookupBankCode(string $bankName): ?string
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

    public function clearSession(Request $request)
    {
        $request->session()->forget(['ai_chat_session', 'kaybot_trade']);
        return response()->json(['ok' => true]);
    }
}