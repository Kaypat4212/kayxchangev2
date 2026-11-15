<?php

namespace App\Services;

use App\Models\GiftCardOrder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class GiftCardDeliveryNotificationService
{
    /** Send only missing notifications, allowing safe admin retries. */
    public function dispatch(GiftCardOrder $order): array
    {
        $order->loadMissing('user');
        $results = ['recipient_email' => 'skipped', 'buyer_email' => 'skipped', 'telegram' => 'skipped'];
        if ($order->status !== 'completed' || trim((string) $order->delivery_code) === '') {
            return ['recipient_email' => 'not_delivered', 'buyer_email' => 'not_delivered', 'telegram' => 'not_delivered'];
        }

        $user = $order->user;
        $recipientEmail = strtolower(trim((string) $order->recipient_email));
        $buyerEmail = strtolower(trim((string) ($user?->email ?? '')));
        $mailAvailable = !in_array((string) config('mail.default'), ['log', 'array'], true);

        if (empty($order->delivery_email_sent_at)) {
            if (!$mailAvailable) {
                $results['recipient_email'] = 'mail_not_configured';
            } else {
                try {
                    Mail::send('emails.gift-card-delivery', ['order' => $order, 'includeCode' => true], function ($message) use ($recipientEmail, $order): void {
                        $message->to($recipientEmail)->subject('Your ' . $order->card_name . ' gift card is ready');
                    });
                    $this->stamp($order, 'delivery_email_sent_at');
                    $results['recipient_email'] = 'accepted';
                } catch (\Throwable $exception) {
                    $results['recipient_email'] = 'failed';
                    Log::error('Gift card delivery email failed', ['order_id' => $order->id, 'reference' => $order->reference, 'error' => $exception->getMessage()]);
                }
            }
        } else {
            $results['recipient_email'] = 'already_sent';
        }

        // If the order was addressed to someone else, send the purchasing user a
        // separate status email without copying the redeemable code.
        if ($user && $buyerEmail !== '' && $buyerEmail !== $recipientEmail && empty($order->buyer_email_sent_at)) {
            if (!$mailAvailable) {
                $results['buyer_email'] = 'mail_not_configured';
            } else {
                try {
                    Mail::send('emails.gift-card-delivery', ['order' => $order, 'includeCode' => false], function ($message) use ($buyerEmail, $order): void {
                        $message->to($buyerEmail)->subject('Your gift card purchase has been delivered');
                    });
                    $this->stamp($order, 'buyer_email_sent_at');
                    $results['buyer_email'] = 'accepted';
                } catch (\Throwable $exception) {
                    $results['buyer_email'] = 'failed';
                    Log::error('Gift card purchaser email failed', ['order_id' => $order->id, 'reference' => $order->reference, 'error' => $exception->getMessage()]);
                }
            }
        } elseif ($buyerEmail === $recipientEmail && $results['recipient_email'] === 'accepted') {
            $this->stamp($order, 'buyer_email_sent_at');
            $results['buyer_email'] = 'accepted';
        } elseif ($user && $buyerEmail !== '' && !empty($order->buyer_email_sent_at)) {
            $results['buyer_email'] = 'already_sent';
        }

        if ($user && $user->telegram_chat_id && $user->telegram_verified && $user->telegram_notifications) {
            if (empty($order->delivery_telegram_sent_at)) {
                try {
                    $url = rtrim((string) config('app.url'), '/') . '/gift-cards';
                    $sent = app(TelegramService::class)->sendMessage(
                        $user->telegram_chat_id,
                        "✅ Your {$order->card_name} · {$order->country} gift card has been delivered.\n"
                            . 'Reference: ' . $order->reference . "\n"
                            . 'Open your account to view the gift-card code securely: ' . $url,
                        null,
                        ['inline_keyboard' => [[['text' => 'View gift-card purchase history', 'url' => $url]]]]
                    );
                    if ($sent) {
                        $this->stamp($order, 'delivery_telegram_sent_at');
                        $results['telegram'] = 'accepted';
                    } else {
                        $results['telegram'] = 'failed';
                    }
                } catch (\Throwable $exception) {
                    $results['telegram'] = 'failed';
                    Log::error('Gift card delivery Telegram notification failed', ['order_id' => $order->id, 'user_id' => $user->id, 'error' => $exception->getMessage()]);
                }
            } else {
                $results['telegram'] = 'already_sent';
            }
        } elseif (!$user?->telegram_notifications) {
            $results['telegram'] = 'notifications_off';
        } else {
            $results['telegram'] = 'not_linked';
        }

        return $results;
    }

    private function stamp(GiftCardOrder $order, string $column): void
    {
        if (Schema::hasColumn('gift_card_orders', $column)) {
            $order->forceFill([$column => now()])->save();
        }
    }
}
