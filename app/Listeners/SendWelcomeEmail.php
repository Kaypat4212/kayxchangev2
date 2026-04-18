<?php

namespace App\Listeners;

use App\Mail\WelcomeEmail;
use App\Models\EmailSetting;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendWelcomeEmail
{
    public function handle(Registered $event): void
    {
        try {
            $settings = EmailSetting::current();
            if (! $settings->welcome_email_enabled) {
                return;
            }
            $this->applyMailConfig($settings);
            Mail::to($event->user->email)->send(new WelcomeEmail($event->user));
        } catch (\Throwable $e) {
            Log::error('WelcomeEmail failed: ' . $e->getMessage());
        }
    }

    private function applyMailConfig(EmailSetting $s): void
    {
        if (! $s->mail_host) return;

        config([
            'mail.default'                      => $s->mail_mailer,
            'mail.mailers.smtp.host'            => $s->mail_host,
            'mail.mailers.smtp.port'            => $s->mail_port,
            'mail.mailers.smtp.username'        => $s->mail_username,
            'mail.mailers.smtp.password'        => $s->mail_password
                ? decrypt($s->mail_password) : null,
            'mail.mailers.smtp.encryption'      => $s->mail_encryption ?: null,
            'mail.from.address'                 => $s->mail_from_address,
            'mail.from.name'                    => $s->mail_from_name,
        ]);
    }
}
