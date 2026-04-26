<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class TestEmail extends Mailable
{
    public string $toAddress;
    public string $sentAt;

    public function __construct(string $toAddress)
    {
        $this->toAddress = $toAddress;
        $this->sentAt    = now()->format('Y-m-d H:i:s T');
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'SMTP Configuration Test');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.test');
    }
}
