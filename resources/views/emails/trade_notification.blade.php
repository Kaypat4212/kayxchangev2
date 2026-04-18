@include('emails.trade_layout', [
    'subject'    => $subject,
    'bodyHtml'   => $bodyHtml,
    'badgeText'  => $badgeText ?? null,
    'badgeColor' => $badgeColor ?? '#00cc00',
    'ctaUrl'     => $ctaUrl ?? null,
    'ctaText'    => $ctaText ?? null,
])
