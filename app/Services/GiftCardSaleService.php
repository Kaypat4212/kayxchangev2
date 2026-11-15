<?php

namespace App\Services;

use App\Models\AdminSetting;
use App\Models\GiftCardRate;
use App\Models\GiftCardSaleOrder;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

class GiftCardSaleService
{
    public function submit(User $user, int $rateId, float $faceValue, string $cardCode, ?string $note = null, ?string $idempotencyToken = null): GiftCardSaleOrder
    {
        if (!filter_var(AdminSetting::getSetting('gift_card_sales_enabled', '0'), FILTER_VALIDATE_BOOLEAN)) {
            throw new RuntimeException('Gift card selling is temporarily unavailable.');
        }
        if ($faceValue < 1 || $faceValue > 10000) throw new RuntimeException('Enter a card value from 1 to 10,000.');
        $cardCode = trim($cardCode);
        if (mb_strlen($cardCode) < 4 || mb_strlen($cardCode) > 2000) throw new RuntimeException('Enter a valid gift card code.');
        $rate = GiftCardRate::query()->whereKey($rateId)->where('is_active', true)
            ->whereIn('country', ['US', 'UK'])->where('buy_rate', '>', 0)->first();
        if (!$rate) throw new RuntimeException('That card is not currently accepted for selling. Choose an active USA or UK card.');

        $token = $idempotencyToken ? substr(hash('sha256', $idempotencyToken), 0, 64) : null;
        $order = DB::transaction(function () use ($user, $rate, $faceValue, $cardCode, $note, $token) {
            if (!filter_var(AdminSetting::getSetting('gift_card_sales_enabled', '0'), FILTER_VALIDATE_BOOLEAN)) {
                throw new RuntimeException('Gift card selling is temporarily unavailable.');
            }
            if ($token && Schema::hasColumn('gift_card_sale_orders', 'bot_submission_token')) {
                $existing = GiftCardSaleOrder::query()->where('bot_submission_token', $token)->where('user_id', $user->id)->lockForUpdate()->first();
                if ($existing) return $existing;
            }
            $lockedRate = GiftCardRate::query()->whereKey($rate->id)->where('is_active', true)
                ->whereIn('country', ['US', 'UK'])->where('buy_rate', '>', 0)->lockForUpdate()->first();
            if (!$lockedRate) throw new RuntimeException('That card is no longer available for selling.');
            $attributes = [
                'user_id' => $user->id,
                'gift_card_rate_id' => $lockedRate->id,
                'reference' => 'GCS-' . now()->format('ymd') . '-' . Str::upper(Str::random(8)),
                'card_name' => $lockedRate->name,
                'country' => $lockedRate->country,
                'currency' => $lockedRate->currency,
                'face_value' => round($faceValue, 2),
                'unit_rate_ngn' => $lockedRate->buy_rate,
                'estimated_ngn' => round($faceValue * (float) $lockedRate->buy_rate, 2),
                'card_code' => $cardCode,
                'customer_note' => $note ? mb_substr(trim($note), 0, 500) : null,
                'status' => 'pending',
            ];
            if ($token && Schema::hasColumn('gift_card_sale_orders', 'bot_submission_token')) $attributes['bot_submission_token'] = $token;
            return GiftCardSaleOrder::create($attributes);
        });

        if (!$order->wasRecentlyCreated) return $order;
        foreach (User::query()->where('is_admin', true)->pluck('id') as $adminId) {
            Notification::create([
                'admin_id' => $adminId,
                'type' => 'warning',
                'title' => 'Gift card sale needs review',
                'message' => $order->reference . ' · ' . $order->card_name . ' ' . $order->country . ' · estimated ₦' . number_format((float) $order->estimated_ngn, 2),
                'data' => ['type' => 'gift_card_sale', 'order_id' => $order->id],
                'is_read' => false,
            ]);
        }
        return $order;
    }
}
