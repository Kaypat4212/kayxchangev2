<?php

namespace App\Services;

use App\Models\BuyTrade;
use App\Models\Notification;
use App\Models\SellTrade;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdminTradeAlertService
{
    protected ?string $token;
    protected ?string $chatId;

    public function __construct()
    {
        $this->token = config('services.telegram.token')
            ?: env('TELEGRAM_BOT_TOKEN')
            ?: env('KAYXCHANGE_TELEGRAM_BOT_TOKEN');

        $this->chatId = config('services.telegram.chat_id')
            ?: env('TELEGRAM_CHAT_ID')
            ?: env('KAYXCHANGE_TELEGRAM_CHAT_ID');
    }

    public function sendTriggeredAlert(string $tradeType, array $data = []): void
    {
        if (!config('trade_alerts.enabled', true)) {
            return;
        }

        if (!$this->shouldNotifyTradeType($tradeType)) {
            return;
        }

        $risk = $this->buildRiskSignals($tradeType, $data);
        $nairaAmount = $this->toFloat($data['naira_amount'] ?? null);
        $highValueThreshold = (float) config('trade_alerts.high_value_ngn_threshold', 500000);
        $isHighValue = $nairaAmount >= $highValueThreshold;

        $badgeParts = ['TRIGGERED'];
        if ($isHighValue) {
            $badgeParts[] = 'HIGH_VALUE';
        }
        if ($risk['is_high_risk']) {
            $badgeParts[] = 'HIGH_RISK';
        }
        if ($risk['duplicate_wallet']) {
            $badgeParts[] = 'DUPLICATE_WALLET';
        }

        $badgeText = implode(', ', $badgeParts);
        $title = 'New ' . strtoupper($tradeType) . ' Trade Triggered';

        $lines = [
            'Badge: ' . $badgeText,
            'Trade Type: ' . strtoupper($tradeType),
            'Reference: ' . ($data['reference'] ?? 'N/A'),
            'User: ' . ($data['user_name'] ?? 'N/A') . ' (' . ($data['user_email'] ?? 'N/A') . ')',
            'Coin: ' . ($data['coin'] ?? 'N/A'),
            'Amount (USD): ' . ($data['usd_amount'] ?? 'N/A'),
            'Amount (NGN): ' . ($data['naira_amount'] ?? 'N/A'),
            'Wallet: ' . ($data['wallet_address'] ?? 'N/A'),
            'Network: ' . ($data['network'] ?? 'N/A'),
            'Status: ' . ($data['status'] ?? 'pending'),
            'Fraud Score: ' . $risk['score'] . '/100 (' . strtoupper($risk['level']) . ')',
            'Risk Signals: ' . (!empty($risk['signals']) ? implode(', ', $risk['signals']) : 'None'),
            'Time: ' . now()->format('Y-m-d H:i:s'),
        ];

        $message = "🚨 *{$title}*\n\n" . implode("\n", array_map(fn ($line) => $this->escapeMarkdown($line), $lines));

        $tradeUrl = rtrim((string) config('app.url'), '/') . '/admin/trades?ref=' . urlencode((string) ($data['reference'] ?? ''));
        $tradeId  = $data['trade_id'] ?? null;

        $actionButtons = [
            [
                ['text' => '🌐 Open Trade', 'url' => $tradeUrl],
                ['text' => '🔔 Notifications', 'url' => rtrim((string) config('app.url'), '/') . '/admin/notifications?status=unread'],
            ],
        ];

        // Add approve/reject quick-action buttons for sell and buy trades (when trade_id is provided)
        if ($tradeId) {
            $type = strtolower($tradeType);
            if (in_array($type, ['sell', 'buy'])) {
                $actionButtons[] = [
                    ['text' => '✅ Approve',  'callback_data' => "approve_{$type}:{$tradeId}"],
                    ['text' => '❌ Reject',   'callback_data' => "reject_{$type}:{$tradeId}"],
                ];
            }
        }

        $this->sendTelegram($message, $this->chatId, $actionButtons);

        if ($isHighValue) {
            $vipChatId = (string) config('trade_alerts.vip_chat_id', '');
            if (!empty($vipChatId)) {
                $this->sendTelegram("💎 *VIP ROUTED ALERT*\n\n" . $message, $vipChatId, $actionButtons);
            }
        }

        Notification::createBroadcast(
            'trade_update',
            $title,
            'Ref: ' . ($data['reference'] ?? 'N/A')
                . ' | ' . ($data['coin'] ?? 'N/A')
                . ' | User: ' . ($data['user_name'] ?? 'N/A')
                . ' | Badge: ' . $badgeText,
            [
                'badge' => $badgeText,
                'trade_type' => strtoupper($tradeType),
                'reference' => $data['reference'] ?? null,
                'coin' => $data['coin'] ?? null,
                'wallet_address' => $data['wallet_address'] ?? null,
                'status' => $data['status'] ?? 'pending',
                'fraud_score' => $risk['score'],
                'fraud_level' => $risk['level'],
                'risk_signals' => $risk['signals'],
                'is_high_value' => $isHighValue,
            ]
        );
    }

    public function sendStatusChangeAlert(string $tradeType, array $data = []): void
    {
        if (!config('trade_alerts.enabled', true) || !config('trade_alerts.notify_status_changes', true)) {
            return;
        }

        $title = strtoupper($tradeType) . ' Status Updated';
        $message = "🔔 *" . $this->escapeMarkdown($title) . "*\n\n"
            . $this->escapeMarkdown('Reference: ' . ($data['reference'] ?? 'N/A')) . "\n"
            . $this->escapeMarkdown('User: ' . ($data['user_name'] ?? 'N/A')) . "\n"
            . $this->escapeMarkdown('Old Status: ' . ($data['old_status'] ?? 'N/A')) . "\n"
            . $this->escapeMarkdown('New Status: ' . ($data['new_status'] ?? 'N/A'));

        $this->sendTelegram($message);
        Notification::createBroadcast('trade_update', $title, 'Ref: ' . ($data['reference'] ?? 'N/A') . ' status changed.', [
            'badge' => 'STATUS_CHANGE',
            'trade_type' => strtoupper($tradeType),
            'reference' => $data['reference'] ?? null,
            'old_status' => $data['old_status'] ?? null,
            'new_status' => $data['new_status'] ?? null,
        ]);
    }

    public function sendEscalationAlert(string $tradeType, array $data = []): void
    {
        if (!config('trade_alerts.enabled', true)) {
            return;
        }

        $title = 'PENDING TRADE ESCALATION';
        $message = "⏰ *" . $this->escapeMarkdown($title) . "*\n\n"
            . $this->escapeMarkdown('Trade Type: ' . strtoupper($tradeType)) . "\n"
            . $this->escapeMarkdown('Reference: ' . ($data['reference'] ?? 'N/A')) . "\n"
            . $this->escapeMarkdown('User: ' . ($data['user_name'] ?? 'N/A')) . "\n"
            . $this->escapeMarkdown('Pending Minutes: ' . ($data['pending_minutes'] ?? 'N/A')) . "\n"
            . $this->escapeMarkdown('Amount (NGN): ' . ($data['naira_amount'] ?? 'N/A'));

        $this->sendTelegram($message, $this->chatId);

        $escChatId = (string) config('trade_alerts.escalation_chat_id', '');
        if (!empty($escChatId)) {
            $this->sendTelegram("🚨 *ESCALATION CHANNEL*\n\n" . $message, $escChatId);
        }

        Notification::createBroadcast('warning', $title, 'Ref: ' . ($data['reference'] ?? 'N/A') . ' pending too long.', [
            'badge' => 'SLA_ESCALATED',
            'trade_type' => strtoupper($tradeType),
            'reference' => $data['reference'] ?? null,
            'pending_minutes' => $data['pending_minutes'] ?? null,
        ]);
    }

    protected function sendTelegram(string $message, ?string $chatId = null, ?array $inlineKeyboard = null): void
    {
        $targetChatId = $chatId ?: $this->chatId;

        if (empty($this->token) || empty($targetChatId)) {
            Log::warning('AdminTradeAlertService: Telegram credentials missing.');
            return;
        }

        try {
            $payload = [
                'chat_id' => $targetChatId,
                'text' => $message,
                'parse_mode' => 'MarkdownV2',
                'disable_web_page_preview' => true,
            ];

            if (!empty($inlineKeyboard)) {
                $payload['reply_markup'] = ['inline_keyboard' => $inlineKeyboard];
            }

            $response = Http::timeout(4)->post("https://api.telegram.org/bot{$this->token}/sendMessage", $payload);

            if (! $response->successful()) {
                Log::warning('AdminTradeAlertService: Telegram send failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('AdminTradeAlertService: Telegram exception', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function escapeMarkdown(string $text): string
    {
        return preg_replace('/([_\*\[\]\(\)~`>#+\-=|{}.!\\\\])/', '\\\\$1', $text) ?? $text;
    }

    protected function shouldNotifyTradeType(string $tradeType): bool
    {
        return match (strtolower($tradeType)) {
            'buy' => (bool) config('trade_alerts.notify_new_buy', true),
            'sell' => (bool) config('trade_alerts.notify_new_sell', true),
            'withdrawal' => (bool) config('trade_alerts.notify_new_withdrawal', true),
            default => true,
        };
    }

    protected function buildRiskSignals(string $tradeType, array $data): array
    {
        $score = 0;
        $signals = [];

        $naira = $this->toFloat($data['naira_amount'] ?? null);
        $highValueThreshold = (float) config('trade_alerts.high_value_ngn_threshold', 500000);
        if ($naira >= $highValueThreshold) {
            $score += 25;
            $signals[] = 'high_value_amount';
        }

        $userId = isset($data['user_id']) ? (int) $data['user_id'] : 0;
        if ($userId > 0) {
            $user = User::find($userId);
            if ($user && $user->created_at && $user->created_at->gt(now()->subDays(7))) {
                $score += 20;
                $signals[] = 'new_account_under_7_days';
            }

            if ($user) {
                $tradeCount = BuyTrade::where('user_id', $userId)->count() + SellTrade::where('user_id', $userId)->count();
                if ($tradeCount <= 1) {
                    $score += 10;
                    $signals[] = 'first_or_second_trade';
                }
            }
        }

        $duplicateWallet = false;
        $walletAddress = trim((string) ($data['wallet_address'] ?? ''));
        if ($walletAddress !== '' && strtoupper($walletAddress) !== 'N/A') {
            $buyDup = BuyTrade::where('wallet_address', $walletAddress)
                ->where('user_id', '!=', $userId)
                ->where('created_at', '>=', now()->subDays(30))
                ->exists();
            $sellDup = SellTrade::where('wallet_address', $walletAddress)
                ->where('user_id', '!=', $userId)
                ->where('created_at', '>=', now()->subDays(30))
                ->exists();
            $duplicateWallet = $buyDup || $sellDup;
            if ($duplicateWallet) {
                $score += 30;
                $signals[] = 'wallet_seen_on_other_account';
            }
        }

        $reference = trim((string) ($data['reference'] ?? ''));
        if ($reference !== '') {
            $dupRefBuy = BuyTrade::where('transaction_ref', $reference)->where('created_at', '>=', now()->subDays(30))->count();
            $dupRefSell = SellTrade::where('transaction_ref', $reference)->where('created_at', '>=', now()->subDays(30))->count();
            if (($dupRefBuy + $dupRefSell) > 1) {
                $score += 40;
                $signals[] = 'duplicate_reference';
            }
        }

        $score = min($score, 100);
        $highRiskThreshold = (int) config('trade_alerts.high_risk_score_threshold', 60);
        $level = $score >= $highRiskThreshold ? 'high' : ($score >= 35 ? 'medium' : 'low');

        return [
            'score' => $score,
            'signals' => $signals,
            'level' => $level,
            'is_high_risk' => $score >= $highRiskThreshold,
            'duplicate_wallet' => $duplicateWallet,
        ];
    }

    protected function toFloat($value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }
        if (is_string($value)) {
            $clean = str_replace([',', '₦', '$', ' '], '', $value);
            return is_numeric($clean) ? (float) $clean : 0.0;
        }
        return 0.0;
    }

    public function shouldEscalate(string $cacheKey): bool
    {
        if (Cache::has($cacheKey)) {
            return false;
        }
        Cache::put($cacheKey, true, now()->addHours(12));
        return true;
    }
}
