<?php

namespace App\Mail;

use App\Models\LoginLog;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoginNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public LoginLog $log;

    public function __construct(LoginLog $log)
    {
        $this->log = $log;
    }

    public function envelope(): Envelope
    {
        $subject = $this->log->status === 'success'
            ? 'Successful Login to Your KayXchange Account'
            : 'Failed Login Attempt on Your KayXchange Account';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $view = $this->log->status === 'success'
            ? 'emails.login_success'
            : 'emails.login_failed';

        return new Content(view: $view);
    }
}
