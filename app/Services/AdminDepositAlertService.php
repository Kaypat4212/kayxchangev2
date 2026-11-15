<?php

namespace App\Services;

use App\Models\AdminSetting;
use App\Models\CryptoDeposit;
use App\Models\Deposit;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AdminDepositAlertService
{
    public function created(Deposit|CryptoDeposit $deposit): void
    {
        try {
            $deposit->loadMissing('user');
            $user = $deposit->user;
            $isCrypto = $deposit instanceof CryptoDeposit;
            $reference = $isCrypto ? ('CRYPTO-DEP-' . $deposit->id) : ($deposit->transaction_ref ?: ('DEP-' . $deposit->id));
            $amount = $isCrypto
                ? number_format((float) $deposit->expected_amount, 8) . ' ' . strtoupper((string) $deposit->currency)
                : '₦' . number_format((float) $deposit->amount, 2);
            $method = $isCrypto
                ? strtoupper((string) data_get($deposit->payment_data, 'provider', 'crypto')) . ' crypto deposit'
                : strtoupper((string) ($deposit->payment_method ?: $deposit->gateway ?: 'deposit'));
            $statusUrl = url($isCrypto ? '/admin/manual-crypto-deposits' : '/admin/deposits');
            $title = 'New deposit received';
            $detail = implode("\n", [
                'Reference: ' . $reference,
                'User: ' . ($user?->name ?: 'Unknown') . ' (' . ($user?->email ?: 'no email') . ')',
                'Type: ' . $method,
                'Amount: ' . $amount,
                'Status: ' . (string) $deposit->status,
                'Time: ' . optional($deposit->created_at)->format('Y-m-d H:i:s') . ' UTC',
                ...(!$isCrypto && $deposit->proof_of_payment ? ['Payment proof: ' . \Illuminate\Support\Facades\Storage::disk('public')->url($deposit->proof_of_payment)] : []),
            ]);

            // Admin notifications are private records (no customer broadcast).
            $adminIds = User::query()->where('is_admin', true)->pluck('id');
            foreach ($adminIds as $adminId) {
                Notification::create([
                    'type' => 'warning',
                    'title' => $title,
                    'message' => $detail,
                    'data' => ['type' => 'deposit_created', 'deposit_id' => $deposit->id, 'deposit_kind' => $isCrypto ? 'crypto' : 'ngn', 'url' => $statusUrl],
                    'admin_id' => (int) $adminId,
                    'is_broadcast' => false,
                    'is_read' => false,
                ]);
            }

            $emailRecipients = User::query()->where('is_admin', true)->whereNotNull('email')->pluck('email')->filter()->unique()->values()->all();
            $fallbackEmail = config('mail.admin_email', env('ADMIN_EMAIL'));
            if (!$emailRecipients && filter_var($fallbackEmail, FILTER_VALIDATE_EMAIL)) $emailRecipients = [$fallbackEmail];
            foreach ($emailRecipients as $recipient) {
                try {
                    Mail::raw($detail . "\nReview: " . $statusUrl, static function ($message) use ($recipient, $title): void {
                        $message->to($recipient)->subject('[KayXchange] ' . $title);
                    });
                } catch (\Throwable $exception) {
                    Log::warning('Admin deposit alert email failed.', ['deposit_id' => $deposit->id, 'to' => $recipient, 'error' => $exception->getMessage()]);
                }
            }

            $token = (string) (AdminSetting::getSetting('telegram_token', '') ?: config('services.telegram.token') ?: env('KAYXCHANGE_TELEGRAM_BOT_TOKEN', ''));
            $chatIds = array_merge(
                User::query()->where('is_admin', true)->whereNotNull('telegram_chat_id')->pluck('telegram_chat_id')->all(),
                array_filter([env('TELEGRAM_CHAT_ID'), env('KAYXCHANGE_TELEGRAM_CHAT_ID'), env('TELEGRAM_OWNER_CHAT_ID')])
            );
            $chatIds = array_values(array_unique(array_filter(array_map('strval', $chatIds))));
            if ($token !== '') {
                $text = '<b>' . e($title) . "</b>\n" . e($detail);
                foreach ($chatIds as $chatId) {
                    try {
                        $response = Http::timeout(7)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                            'chat_id' => $chatId,
                            'text' => $text,
                            'parse_mode' => 'HTML',
                            'disable_web_page_preview' => true,
                            'reply_markup' => ['inline_keyboard' => [[['text' => 'Review deposit', 'url' => $statusUrl]]]],
                        ]);
                        if (!$response->successful() || !$response->json('ok')) {
                            Log::warning('Admin deposit alert Telegram delivery failed.', ['deposit_id' => $deposit->id, 'chat_id' => $chatId, 'response' => $response->json('description')]);
                        }
                    } catch (\Throwable $exception) {
                        Log::warning('Admin deposit alert Telegram delivery failed.', ['deposit_id' => $deposit->id, 'chat_id' => $chatId, 'error' => $exception->getMessage()]);
                    }
                }
            } else {
                Log::warning('Admin deposit Telegram alert skipped: bot token is not configured.', ['deposit_id' => $deposit->id]);
            }
        } catch (\Throwable $exception) {
            Log::error('Admin deposit alert failed.', ['deposit_id' => $deposit->id ?? null, 'error' => $exception->getMessage()]);
        }
    }
}
