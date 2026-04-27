<?php

namespace App\Services;

use App\Mail\RateUpdateMail;
use App\Models\CryptoRate;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RateNotificationService
{
    public function __construct(private TelegramService $telegram) {}

    /**
     * Build the human-readable rates block shared by Telegram + email.
     *
     * @return array{rates: \Illuminate\Database\Eloquent\Collection, lines: string}
     */
    private function buildRateBlock(): array
    {
        $rates = CryptoRate::orderBy('coin')->get();

        $lines = '';
        foreach ($rates as $r) {
            $lines .= "🪙 *{$r->coin}*\n"
                . "   Buy : ₦" . number_format($r->buy_rate,  2) . "\n"
                . "   Sell: ₦" . number_format($r->sell_rate, 2) . "\n\n";
        }

        return compact('rates', 'lines');
    }

    /**
     * Build the HTML rates table used in the email body.
     */
    private function buildEmailHtml(string $context): string
    {
        $rates = CryptoRate::orderBy('coin')->get();
        $time  = now()->setTimezone('Africa/Lagos')->format('d M Y, g:i A') . ' (WAT)';

        $rows = '';
        foreach ($rates as $r) {
            $rows .= "
            <tr>
                <td style='padding:8px 12px;font-weight:700;color:#00cc00;'>🪙 {$r->coin}</td>
                <td style='padding:8px 12px;'>₦" . number_format($r->buy_rate,  2) . "</td>
                <td style='padding:8px 12px;'>₦" . number_format($r->sell_rate, 2) . "</td>
            </tr>";
        }

        $contextLine = $context === 'scheduled'
            ? 'Here are your scheduled rate update for today.'
            : '⚡ Our admin just updated the platform rates.';

        return "
        <p style='margin:0 0 16px;font-size:15px;line-height:1.6;color:#ccc;'>{$contextLine}</p>
        <table width='100%' cellpadding='0' cellspacing='0' style='border-collapse:collapse;border:1px solid rgba(255,255,255,0.1);border-radius:10px;overflow:hidden;margin-bottom:20px;'>
            <thead>
                <tr style='background:rgba(0,204,0,0.1);'>
                    <th style='padding:10px 12px;text-align:left;color:#00cc00;font-size:13px;'>Coin</th>
                    <th style='padding:10px 12px;text-align:left;color:#00cc00;font-size:13px;'>Buy Rate</th>
                    <th style='padding:10px 12px;text-align:left;color:#00cc00;font-size:13px;'>Sell Rate</th>
                </tr>
            </thead>
            <tbody>{$rows}</tbody>
        </table>
        <p style='margin:0 0 8px;font-size:13px;color:#888;'>🕐 Updated: {$time}</p>
        <p style='margin:0;font-size:13px;color:#888;'>Log in to place a trade at these rates before they change.</p>";
    }

    /**
     * Send rate notifications to ALL opted-in users (Telegram + email).
     *
     * @param  string  $context  'admin_update' | 'scheduled'
     */
    public function notifyAllUsers(string $context = 'admin_update'): void
    {
        ['rates' => $rates, 'lines' => $rateLines] = $this->buildRateBlock();

        if ($rates->isEmpty()) {
            Log::info('[RateNotification] No rates found — skipping notification.');
            return;
        }

        $contextLabel = $context === 'scheduled' ? '🕐 Scheduled Rate Update' : '⚡ Rate Update Alert';
        $time = now()->setTimezone('Africa/Lagos')->format('d M Y, g:i A') . ' (WAT)';

        // ── Get all subscribed users ────────────────────────────────────────
        $users = User::where('is_admin', false)
            ->where(function ($q) {
                $q->where(function ($q2) {
                    // Telegram-eligible
                    $q2->where('telegram_notifications', true)
                       ->whereNotNull('telegram_chat_id')
                       ->where('telegram_verified', true);
                })->orWhere(function ($q2) {
                    // Email-eligible (all active users get email)
                    $q2->whereNotNull('email')
                       ->where('email_verified_at', '!=', null);
                });
            })
            ->get();

        $tgSent    = 0;
        $emailSent = 0;

        foreach ($users as $user) {
            /** @var \App\Models\User $user */
            // ── Telegram ─────────────────────────────────────────────────
            if ($user->telegram_chat_id && $user->telegram_notifications && $user->telegram_verified) {
                try {
                    $msg = "📈 *{$contextLabel} — KayXchange*\n\n"
                         . "Hello *{$user->name}*! 👋\n\n"
                         . "Current trading rates:\n\n"
                         . $rateLines
                         . "🕐 *{$time}*\n\n"
                         . "Tap below to start trading 🚀";

                    $keyboard = ['inline_keyboard' => [[
                        ['text' => '💰 Buy Crypto',  'url' => rtrim(config('app.url'), '/') . '/buy'],
                        ['text' => '💵 Sell Crypto', 'url' => rtrim(config('app.url'), '/') . '/sell'],
                    ]]];

                    $this->telegram->sendMessage($user->telegram_chat_id, $msg, 'Markdown', $keyboard);
                    $tgSent++;
                } catch (\Throwable $e) {
                    Log::warning("[RateNotification] Telegram failed for user {$user->id}: " . $e->getMessage());
                }
            }

            // ── Email ─────────────────────────────────────────────────────
            if ($user->email && $user->email_verified_at) {
                try {
                    $bodyHtml = $this->buildEmailHtml($context);
                    $appUrl   = preg_replace('#/public$#', '', rtrim(config('app.url'), '/'));

                    Mail::to($user->email)->send(new RateUpdateMail(
                        user:      $user,
                        badgeText: $contextLabel,
                        bodyHtml:  $bodyHtml,
                        ctaUrl:    $appUrl . '/dashboard',
                    ));
                    $emailSent++;
                } catch (\Throwable $e) {
                    Log::warning("[RateNotification] Email failed for user {$user->id}: " . $e->getMessage());
                }
            }
        }

        Log::info("[RateNotification] Dispatched — context={$context}, tg={$tgSent}, email={$emailSent}");

        // ── Admin Telegram alert ──────────────────────────────────────────────
        $this->notifyAdmin($context, $rateLines, $time, $tgSent, $emailSent);
    }

    /**
     * Send a brief rate-update confirmation to all admin Telegram chats.
     */
    private function notifyAdmin(string $context, string $rateLines, string $time, int $tgSent, int $emailSent): void
    {
        try {
            $trigger = $context === 'scheduled' ? '🕐 Scheduled job' : '👤 Manual admin update';

            $msg = "🔔 *Rate Update Dispatched — KayXchange*\n\n"
                 . "📌 *Trigger:* {$trigger}\n"
                 . "🕐 *Time:* {$time}\n\n"
                 . "*Current Rates:*\n\n"
                 . $rateLines
                 . "📨 *Notified:* {$tgSent} Telegram, {$emailSent} email";

            $this->telegram->sendToAdminChats($msg, null, 'Markdown');
        } catch (\Throwable $e) {
            Log::warning('[RateNotification] Admin Telegram alert failed: ' . $e->getMessage());
        }
    }
}
