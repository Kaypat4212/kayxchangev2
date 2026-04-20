<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\PriceAlert;
use App\Models\CryptoRate;
use App\Services\CoinGeckoService;
use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class CheckPriceAlerts extends Command
{
    protected $signature   = 'alerts:check';
    protected $description = 'Check all active price alerts and notify users when triggered';

    // CoinGecko coin IDs for market price lookup
    private const COIN_IDS = [
        'BTC'  => 'bitcoin',
        'ETH'  => 'ethereum',
        'USDT' => 'tether',
        'SOL'  => 'solana',
        'BNB'  => 'binancecoin',
    ];

    public function handle(CoinGeckoService $coingecko, TelegramService $telegram): int
    {
        $alerts = PriceAlert::where('is_active', true)
            ->whereNull('triggered_at')
            ->with('user')
            ->get();

        if ($alerts->isEmpty()) {
            return self::SUCCESS;
        }

        // Fetch platform rates (buy+sell for NGN)
        $platformRates = CryptoRate::all()->keyBy(fn($r) => strtoupper($r->coin));

        // Fetch market prices (USD) from CoinGecko — only coins needed
        $marketCoins   = $alerts->where('type', 'market')->pluck('coin')->unique()->values();
        $marketPrices  = [];
        if ($marketCoins->isNotEmpty()) {
            $ids = $marketCoins->map(fn($c) => self::COIN_IDS[strtoupper($c)] ?? strtolower($c))->all();
            $raw = $coingecko->getCryptoPrices($ids);
            foreach ($raw as $item) {
                $sym = strtoupper($item['symbol'] ?? '');
                $marketPrices[$sym] = $item['price_usd'] ?? 0;
            }
        }

        foreach ($alerts as $alert) {
            $coin      = strtoupper($alert->coin);
            $target    = (float) $alert->target_price;
            $direction = $alert->direction;

            if ($alert->type === 'platform') {
                $rate = $platformRates[$coin] ?? null;
                if (!$rate) continue;
                // Use buy_rate as the reference for "platform price"
                $current = (float) $rate->buy_rate;
                $unit    = 'NGN (platform buy rate)';
            } else {
                $current = $marketPrices[$coin] ?? 0;
                if ($current <= 0) continue;
                $unit = 'USD (market price)';
            }

            $triggered = match($direction) {
                'above' => $current >= $target,
                'below' => $current <= $target,
                default => false,
            };

            if (!$triggered) continue;

            // Fire alert
            $this->fireAlert($alert, $current, $target, $unit, $telegram);
        }

        return self::SUCCESS;
    }

    private function fireAlert(PriceAlert $alert, float $current, float $target, string $unit, TelegramService $telegram): void
    {
        try {
            $alert->update(['triggered_at' => now(), 'is_active' => false]);

            $user      = $alert->user;
            $coin      = strtoupper($alert->coin);
            $direction = $alert->direction === 'above' ? 'risen above' : 'fallen below';
            $currency  = $alert->type === 'platform' ? '₦' : '$';
            $typeLabel = $alert->type === 'platform' ? 'Platform Rate' : 'Market Price';

            $title   = "🔔 Price Alert: {$coin} {$typeLabel}";
            $message = "{$coin} {$typeLabel} has {$direction} your target of {$currency}" .
                       number_format($target, 2) . ". Current: {$currency}" . number_format($current, 2) . ".";

            // In-app notification
            if ($alert->notify_app && $user) {
                Notification::create([
                    'user_id' => $user->id,
                    'type'    => 'price_alert',
                    'title'   => $title,
                    'message' => $message,
                ]);
            }

            // Telegram notification
            if ($alert->notify_telegram && $user && $user->telegram_chat_id) {
                $tgMsg = "🔔 *Price Alert Triggered!*\n\n" .
                         "🪙 Coin: *{$coin}*\n" .
                         "📊 Type: *{$typeLabel}*\n" .
                         "📈 Condition: *{$alert->direction}* {$currency}" . number_format($target, 2) . "\n" .
                         "💰 Current: *{$currency}" . number_format($current, 2) . "*\n\n" .
                         "_This alert has been automatically deactivated._";
                $telegram->sendMessage((int) $user->telegram_chat_id, $tgMsg, 'Markdown');
            }

            // Email notification
            if ($alert->notify_email && $user) {
                try {
                    Mail::send([], [], function ($mail) use ($user, $title, $message, $coin, $current, $target, $direction, $typeLabel, $currency) {
                        $mail->to($user->email)
                            ->subject("🔔 KayXchange Price Alert: {$coin}")
                            ->html(
                                "<h2 style='color:#16a34a'>Price Alert Triggered</h2>" .
                                "<p><strong>{$coin} {$typeLabel}</strong> has {$direction} your target.</p>" .
                                "<table style='border-collapse:collapse;width:100%;max-width:400px'>" .
                                "<tr><td style='padding:8px;border:1px solid #e5e7eb'><strong>Target</strong></td>" .
                                "<td style='padding:8px;border:1px solid #e5e7eb'>{$currency}" . number_format($target, 2) . "</td></tr>" .
                                "<tr><td style='padding:8px;border:1px solid #e5e7eb'><strong>Current</strong></td>" .
                                "<td style='padding:8px;border:1px solid #e5e7eb'>{$currency}" . number_format($current, 2) . "</td></tr>" .
                                "</table>" .
                                "<p style='color:#6b7280;font-size:13px;margin-top:16px'>This alert has been deactivated. You can set new alerts from your <a href='" . config('app.url') . "/price-alerts'>price alerts page</a>.</p>"
                            );
                    });
                } catch (\Throwable $e) {
                    Log::warning("Price alert email failed for user {$user->id}: " . $e->getMessage());
                }
            }

            Log::info("Price alert #{$alert->id} fired for user #{$user?->id} — {$coin} {$alert->direction} {$target}");
        } catch (\Throwable $e) {
            Log::error("Price alert fire failed #{$alert->id}: " . $e->getMessage());
        }
    }
}
