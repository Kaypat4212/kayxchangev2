<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RateUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User   $user,
        public string $badgeText,
        public string $bodyHtml,
        public string $ctaUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '📈 ' . $this->badgeText . ' — ' . config('app.name'));
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.trade_notification',
            with: [
                'subject'    => '📈 ' . $this->badgeText,
                'bodyHtml'   => $this->bodyHtml,
                'badgeText'  => $this->badgeText,
                'badgeColor' => '#00cc00',
                'ctaUrl'     => $this->ctaUrl,
                'ctaText'    => '📊 Trade Now',
            ],
        );
    }
}
