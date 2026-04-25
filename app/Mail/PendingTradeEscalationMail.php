<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PendingTradeEscalationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $tradeType;
    public array  $data;
    public string $tradeUrl;

    public function __construct(string $tradeType, array $data, string $tradeUrl)
    {
        $this->tradeType = strtoupper($tradeType);
        $this->data      = $data;
        $this->tradeUrl  = $tradeUrl;
    }

    public function envelope(): Envelope
    {
        $ref = $this->data['reference'] ?? 'N/A';
        return new Envelope(
            subject: "⏰ Pending {$this->tradeType} Trade Escalation — {$ref}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pending-trade-escalation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
