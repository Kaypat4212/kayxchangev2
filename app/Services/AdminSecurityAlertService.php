<?php

namespace App\Services;

use App\Models\User;
use App\Models\AdminSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AdminSecurityAlertService
{
    public function credentialChanged(User $actor, array $labels): void
    {
        $this->notify(
            'Admin integration credentials changed',
            'The following encrypted integration credential(s) were changed: ' . implode(', ', $labels),
            $actor
        );
    }

    public function credentialCopied(User $actor, string $label): void
    {
        $this->notify(
            'Payment credential copied',
            'An administrator requested a copy of the saved ' . $label . '.',
            $actor
        );
    }

    public function credentialCleared(User $actor, array $labels): void
    {
        $this->notify(
            'Admin integration credentials cleared',
            'Saved encrypted credential(s) were cleared: ' . implode(', ', $labels),
            $actor
        );
    }

    public function credentialRevealed(User $actor, string $label): void
    {
        $this->notify('Payment credential revealed', 'An administrator revealed the saved ' . $label . '.', $actor);
    }

    public function criticalAdminChange(User $actor, string $area, string $action): void
    {
        $this->notify('Sensitive admin change', $area . ' was changed via ' . $action . '.', $actor);
    }

    private function notify(string $title, string $detail, User $actor): void
    {
        $ip = (string) request()->ip();
        $time = now()->toDateTimeString() . ' UTC';
        $body = "KayXchange admin security alert\n\n{$title}\n{$detail}\n\nAdmin: {$actor->name} ({$actor->email})\nIP: {$ip}\nTime: {$time}\n\nNo credential values are included in this alert.";

        try {
            $emails = User::query()->where('is_admin', true)->whereNotNull('email')->pluck('email')->filter()->unique()->values()->all();
            $fallback = config('mail.admin_email', env('ADMIN_EMAIL'));
            if (!$emails && filter_var($fallback, FILTER_VALIDATE_EMAIL)) $emails = [$fallback];
            if ($emails) {
                Mail::raw($body, static function ($message) use ($emails, $title): void {
                    $message->to($emails)->subject('[KayXchange Security] ' . $title);
                });
            } else {
                Log::warning('Admin security alert email skipped: no admin recipient is configured.', ['title' => $title]);
            }
        } catch (\Throwable $e) {
            Log::warning('Admin security alert email failed.', ['title' => $title, 'error' => $e->getMessage()]);
        }

        try {
            $token = (string) (AdminSetting::getSetting('telegram_token', '')
                ?: config('services.telegram.token')
                ?: env('KAYXCHANGE_TELEGRAM_BOT_TOKEN', ''));
            $chatIds = array_merge(
                User::query()->where('is_admin', true)->whereNotNull('telegram_chat_id')->pluck('telegram_chat_id')->all(),
                array_filter([env('TELEGRAM_CHAT_ID'), env('KAYXCHANGE_TELEGRAM_CHAT_ID'), env('TELEGRAM_OWNER_CHAT_ID')])
            );
            $chatIds = array_values(array_unique(array_filter(array_map('strval', $chatIds))));
            $telegramText = '<b>' . e($title) . "</b>\n" . e($detail)
                . "\nAdmin: " . e($actor->name . ' (' . $actor->email . ')')
                . "\nIP: " . e($ip) . "\nTime: " . e($time)
                . "\nCredential values are not included.";
            if ($token !== '') {
                foreach ($chatIds as $chatId) {
                    Http::timeout(5)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                        'chat_id' => $chatId,
                        'text' => $telegramText,
                        'parse_mode' => 'HTML',
                        'disable_web_page_preview' => true,
                    ]);
                }
            } else {
                Log::warning('Admin security alert Telegram delivery skipped: no bot token is configured.', ['title' => $title]);
            }
        } catch (\Throwable $e) {
            Log::warning('Admin security alert Telegram delivery failed.', ['title' => $title, 'error' => $e->getMessage()]);
        }
    }
}
