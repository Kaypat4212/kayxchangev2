<?php

namespace App\Services;

use App\Models\EmailSetting;
use Illuminate\Support\Facades\Mail;

class EmailTransportConfigurator
{
    /** Load the admin-managed SMTP configuration for this request/command. */
    public function applySavedSmtpSettings(): bool
    {
        $settings = EmailSetting::query()->first();
        if (!$settings || trim((string) $settings->mail_host) === '') return false;

        $password = null;
        if (filled($settings->mail_password)) {
            try {
                $password = decrypt($settings->mail_password);
            } catch (\Throwable) {
                // Older installations may have initialized this row from a
                // plaintext environment value before database encryption.
                $password = $settings->mail_password;
            }
        }

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.host' => $settings->mail_host,
            'mail.mailers.smtp.port' => (int) $settings->mail_port,
            'mail.mailers.smtp.username' => $settings->mail_username,
            'mail.mailers.smtp.password' => $password,
            'mail.mailers.smtp.encryption' => $settings->mail_encryption ?: null,
            'mail.from.address' => $settings->mail_from_address,
            'mail.from.name' => $settings->mail_from_name ?: config('app.name'),
        ]);
        Mail::forgetMailers();

        return true;
    }
}
