<?php

namespace App\Listeners;

use App\Models\MailDeliveryLog;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Email;

class TrackOutboundMail
{
    public function handle(MessageSending|MessageSent $event): void
    {
        if ($event instanceof MessageSending) {
            $this->recordSending($event);
            return;
        }

        $this->recordAccepted($event);
    }

    private function recordSending(MessageSending $event): void
    {
        try {
            $traceId = (string) Str::uuid();
            $event->message->getHeaders()->addTextHeader('X-KayXchange-Delivery-Trace', $traceId);
            $to = collect($event->message->getTo())->map(fn ($address) => $address->getAddress())->filter()->take(1)->first();
            $subject = mb_substr(trim((string) $event->message->getSubject()), 0, 255);
            $retryType = match ($subject) {
                'SMTP Configuration Test' => 'smtp_test',
                default => str_starts_with($subject, 'Daily KayXchange trade summary - ') ? 'daily_digest' : null,
            };
            $retryOf = $event->message->getHeaders()->get('X-KayXchange-Retry-Of')?->getBodyAsString();
            $parent = ctype_digit((string) $retryOf) ? MailDeliveryLog::find((int) $retryOf) : null;

            MailDeliveryLog::create([
                'trace_id' => $traceId,
                'message_id' => mb_substr((string) ($event->message->getHeaders()->get('Message-ID')?->getBodyAsString() ?? ''), 0, 255) ?: null,
                'recipient' => $to,
                'subject' => $subject ?: '(no subject)',
                'mailer' => (string) config('mail.default', 'smtp'),
                'status' => 'sending',
                'retry_type' => $retryType,
                'retry_of_id' => $parent?->id,
                'attempts' => $parent ? $parent->attempts : 1,
            ]);
        } catch (\Throwable $exception) {
            // Logging must never prevent a legitimate outgoing email.
            Log::notice('Could not record outbound mail metadata.', ['exception_class' => $exception::class]);
        }
    }

    private function recordAccepted(MessageSent $event): void
    {
        try {
            $message = $event->sent->getSymfonySentMessage()->getOriginalMessage();
            if (!$message instanceof Email) return;
            $traceId = $message->getHeaders()->get('X-KayXchange-Delivery-Trace')?->getBodyAsString();
            if (!$traceId) return;

            MailDeliveryLog::where('trace_id', $traceId)->update([
                'message_id' => mb_substr($event->sent->getMessageId(), 0, 255),
                'status' => 'accepted',
                'accepted_at' => now(),
            ]);
        } catch (\Throwable $exception) {
            Log::notice('Could not update outbound mail delivery metadata.', ['exception_class' => $exception::class]);
        }
    }

    public static function markLatestFailure(string $recipient, string $subject, string $error): void
    {
        try {
            $safeError = preg_replace('/\s+/', ' ', strip_tags($error)) ?? 'Mail transport failed.';
            $safeError = preg_replace('/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/i', '[email]', $safeError) ?? $safeError;
            $safeError = preg_replace('/(password|secret|token|api[_-]?key)\s*[:=]\s*[^\s,;]+/i', '$1=[redacted]', $safeError) ?? $safeError;
            MailDeliveryLog::where('status', 'sending')
                ->where('recipient', $recipient)
                ->where('subject', $subject)
                ->where('created_at', '>=', now()->subMinutes(10))
                ->latest('id')
                ->first()?->update([
                    'status' => 'failed',
                    'last_error' => mb_substr($safeError, 0, 1000),
                ]);
        } catch (\Throwable $exception) {
            Log::notice('Could not mark outbound mail attempt as failed.', ['exception_class' => $exception::class]);
        }
    }
}
