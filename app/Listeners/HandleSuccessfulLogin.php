<?php

namespace App\Listeners;

use App\Mail\LoginNotificationMail;
use App\Models\EmailSetting;
use App\Models\LoginLog;
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
            $logEntry = LoginLog::create([
                'user_id'    => $event->user->id,
                'email'      => $event->user->email,
                'ip_address' => $this->request->ip(),
                'user_agent' => $this->request->userAgent(),
                'status'     => 'success',
            ]);

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
