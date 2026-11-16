<?php

namespace App\Listeners;

use App\Mail\LoginNotificationMail;
use App\Models\EmailSetting;
use App\Models\LoginLog;
use App\Models\Notification;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class HandleSuccessfulLogin
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function handle(Login $event): void
    {
        try {
            // Record in login_logs
            $userAgent = substr((string) $this->request->userAgent(), 0, 1000);
            $newBrowser = ! LoginLog::query()
                ->where('user_id', $event->user->id)
                ->where('status', 'success')
                ->where('user_agent', $userAgent)
                ->exists();
            $logEntry = LoginLog::create([
                'user_id'    => $event->user->id,
                'email'      => $event->user->email,
                'ip_address' => $this->request->ip(),
                'user_agent' => $userAgent,
                'status'     => 'success',
            ]);

            if ($newBrowser) {
                $message = 'A sign-in was detected from a browser or device not seen on your account before. IP: ' . ($logEntry->ip_address ?: 'unavailable') . '. If this was not you, change your password and contact support.';
                Notification::create([
                    'user_id' => $event->user->id,
                    'type' => 'account_security',
                    'title' => 'New sign-in detected',
                    'message' => $message,
                ]);
                if ($event->user->telegram_notifications && $event->user->telegram_chat_id) {
                    try {
                        app(\App\Services\TelegramService::class)->sendMessage(
                            (int) $event->user->telegram_chat_id,
                            "🔐 New KayXchange sign-in detected\nIP: " . ($logEntry->ip_address ?: 'unavailable') . "\nIf this was not you, secure your account and contact support."
                        );
                    } catch (\Throwable $telegramError) {
                        Log::notice('New sign-in Telegram alert could not be sent.', ['user_id' => $event->user->id, 'error' => $telegramError->getMessage()]);
                    }
                }
            }

            // Send notification email if enabled
            $settings = EmailSetting::current();
            if (! $settings->login_success_email_enabled) {
                return;
            }
            $this->applyMailConfig($settings);
            Mail::to($event->user->email)->send(new LoginNotificationMail($logEntry));
        } catch (\Throwable $e) {
            Log::error('HandleSuccessfulLogin failed: ' . $e->getMessage());
        }
    }

    private function applyMailConfig(EmailSetting $s): void
    {
        if (! $s->mail_host) return;
        config([
            'mail.default'                 => $s->mail_mailer,
            'mail.mailers.smtp.host'       => $s->mail_host,
            'mail.mailers.smtp.port'       => $s->mail_port,
            'mail.mailers.smtp.username'   => $s->mail_username,
            'mail.mailers.smtp.password'   => $s->mail_password ? decrypt($s->mail_password) : null,
            'mail.mailers.smtp.encryption' => $s->mail_encryption ?: null,
            'mail.from.address'            => $s->mail_from_address,
            'mail.from.name'               => $s->mail_from_name,
        ]);
    }
}
