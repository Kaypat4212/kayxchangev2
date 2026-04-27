<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int         $id
 * @property string      $mail_mailer
 * @property string      $mail_host
 * @property int         $mail_port
 * @property string      $mail_username
 * @property string|null $mail_password
 * @property string|null $mail_encryption
 * @property string      $mail_from_address
 * @property string      $mail_from_name
 * @property string|null $support_email
 * @property string|null $security_email
 * @property bool        $welcome_email_enabled
 * @property bool        $login_success_email_enabled
 * @property bool        $login_failed_email_enabled
 */
class EmailSetting extends Model
{
    protected $fillable = [
        'mail_mailer',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_encryption',
        'mail_from_address',
        'mail_from_name',
        'support_email',
        'security_email',
        'welcome_email_enabled',
        'login_success_email_enabled',
        'login_failed_email_enabled',
    ];

    protected $casts = [
        'welcome_email_enabled'       => 'boolean',
        'login_success_email_enabled' => 'boolean',
        'login_failed_email_enabled'  => 'boolean',
        'mail_port'                   => 'integer',
    ];

    /**
     * Get the singleton settings row, creating defaults if absent.
     */
    public static function current(): self
    {
        return self::firstOrCreate([], [
            'mail_mailer'                  => config('mail.default', 'smtp') ?? 'smtp',
            'mail_host'                    => config('mail.mailers.smtp.host', '') ?? '',
            'mail_port'                    => config('mail.mailers.smtp.port', 587) ?? 587,
            'mail_username'                => config('mail.mailers.smtp.username') ?? '',
            'mail_password'                => config('mail.mailers.smtp.password') ?? '',
            'mail_encryption'              => config('mail.mailers.smtp.encryption', 'tls') ?? 'tls',
            'mail_from_address'            => config('mail.from.address', '') ?? '',
            'mail_from_name'               => config('mail.from.name', 'KayXchange') ?? 'KayXchange',
            'support_email'                => config('app.support_email', '') ?? '',
            'security_email'               => config('app.security_email', '') ?? '',
            'welcome_email_enabled'        => true,
            'login_success_email_enabled'  => true,
            'login_failed_email_enabled'   => true,
        ]);
    }
}
