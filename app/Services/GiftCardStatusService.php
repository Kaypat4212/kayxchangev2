<?php

namespace App\Services;

use App\Models\GiftCardOrder;
use App\Models\User;
use Illuminate\Support\Collection;

class GiftCardStatusService
{
    /** Current purchase status without exposing the gift-card code in chat. */
    public function forUser(User $user, ?string $query = null): string
    {
        preg_match('/GC-\d{6}-[A-Z0-9]{8}/i', (string) $query, $match);
        $reference = isset($match[0]) ? strtoupper($match[0]) : null;

        if ($reference) {
            $orders = GiftCardOrder::query()->where('user_id', $user->id)
                ->where('reference', $reference)->limit(1)->get();
            if ($orders->isEmpty()) return "I couldn't find gift-card purchase {$reference} on your account. Check the reference and try again.";
        } else {
            $orders = GiftCardOrder::query()->where('user_id', $user->id)->latest()->limit(5)->get();
        }

        if ($orders->isEmpty()) {
            return 'There are no gift-card purchases on your account yet. Start a purchase with /buygiftcard or open ' . url('/gift-cards') . '.';
        }

        $lines = [$reference ? '🎁 Gift-card purchase status' : '🎁 Your recent gift-card purchases'];
        foreach ($orders as $order) {
            $lines[] = '';
            $lines[] = $this->statusIcon((string) $order->status) . ' ' . $order->card_name . ' · ' . $order->country;
            $lines[] = 'Face value: ' . $order->currency . ' ' . number_format((float) $order->face_value, 2)
                . ' · Paid: ₦' . number_format((float) $order->total_ngn, 2);
            $lines[] = 'Status: ' . $this->statusText((string) $order->status);
            $lines[] = 'Reference: ' . $order->reference;
            $lines[] = 'Placed: ' . $order->created_at?->timezone('Africa/Lagos')->format('d M Y, H:i') . ' WAT';
            if ($order->status === 'completed') {
                $lines[] = 'Your card was marked delivered. Sign in and open Gift Cards → Purchase history to view the code securely.';
            } elseif ($order->status === 'pending') {
                $lines[] = 'The request is waiting for the fulfillment team. We will notify you when it is ready.';
            } elseif ($order->status === 'rejected') {
                $lines[] = 'This request was declined and its Naira charge was refunded.';
            }
        }
        $lines[] = '';
        $lines[] = 'Purchase history: ' . url('/gift-cards');
        return implode("\n", $lines);
    }

    private function statusText(string $status): string
    {
        return match (strtolower($status)) {
            'completed' => 'Delivered',
            'pending' => 'Pending fulfillment',
            'rejected' => 'Declined · refunded',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    private function statusIcon(string $status): string
    {
        return match (strtolower($status)) {
            'completed' => '✅',
            'pending' => '⏳',
            'rejected' => '❌',
            default => '🔄',
        };
    }
}
