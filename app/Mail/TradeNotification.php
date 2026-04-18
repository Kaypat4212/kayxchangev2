<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Generic trade/event email driven by the email_templates table.
 *
 * Usage:
 *   Mail::to($user)->send(new TradeNotification(
 *       user: $user,
 *       templateKey: 'buy_trade_completed',
 *       data: ['amount' => '0.005', 'currency' => 'BTC', ...],
 *       badge: ['text' => 'Order Completed', 'color' => '#00cc00'],
 *       ctaUrl: route('dashboard'),
 *       ctaText: 'View Dashboard',
 *   ));
 */
class TradeNotification extends Mailable
{
    use Queueable, SerializesModels;

    public string  $resolvedSubject;
    public string  $resolvedBody;
    public ?string $badgeText;
    public string  $badgeColor;
    public ?string $ctaUrl;
    public ?string $ctaText;

    public function __construct(
        public User   $user,
        string        $templateKey,
        array         $data = [],
        array         $badge = [],
        ?string       $ctaUrl = null,
        ?string       $ctaText = null,
    ) {
        // Merge common tokens
        $data = array_merge([
            'user_name' => $user->name,
            'app_name'  => config('app.name'),
        ], $data);

        $resolved = EmailTemplate::resolve($templateKey, $data);

        $this->resolvedSubject = $resolved['subject'] ?? ucwords(str_replace('_', ' ', $templateKey));
        $this->resolvedBody    = $resolved['body']    ?? '<p>Please log in to view your trade details.</p>';
        $this->badgeText       = $badge['text']  ?? null;
        $this->badgeColor      = $badge['color'] ?? '#00cc00';
        $this->ctaUrl          = $ctaUrl;
        $this->ctaText         = $ctaText;
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->resolvedSubject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.trade_notification',
            with: [
                'subject'    => $this->resolvedSubject,
                'bodyHtml'   => $this->resolvedBody,
                'badgeText'  => $this->badgeText,
                'badgeColor' => $this->badgeColor,
                'ctaUrl'     => $this->ctaUrl,
                'ctaText'    => $this->ctaText,
            ],
        );
    }
}
