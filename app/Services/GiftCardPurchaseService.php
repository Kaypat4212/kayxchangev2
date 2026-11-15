<?php

namespace App\Services;

use App\Exceptions\GiftCardPurchaseException;
use App\Models\AdminSetting;
use App\Models\GiftCardOrder;
use App\Models\GiftCardRate;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

class GiftCardPurchaseService
{
    public function quote(GiftCardRate $rate, float $faceValue): array
    {
        if ($faceValue < 1 || $faceValue > 10000 || $rate->sell_rate <= 0 || $rate->usd_exchange_rate <= 0) {
            throw new GiftCardPurchaseException('This card or face value is not available.');
        }
        $usdFaceValue = round($faceValue * (float) $rate->usd_exchange_rate, 2);
        $baseNgn = round($faceValue * (float) $rate->sell_rate, 2);
        $feeNgn = max(0, (float) ($rate->purchase_markup_ngn ?? 0));
        return [
            'usd_face_value' => $usdFaceValue,
            'base_total_ngn' => $baseNgn,
            'service_fee_ngn' => $feeNgn,
            'total_ngn' => round($baseNgn + $feeNgn, 2),
            'ngn_per_usd' => round((float) $rate->sell_rate / (float) $rate->usd_exchange_rate, 2),
        ];
    }

    public function purchase(User $user, int $rateId, float $faceValue, string $recipientEmail, string $pin, ?string $note = null, ?string $idempotencyToken = null): GiftCardOrder
    {
        if (!filter_var(AdminSetting::getSetting('gift_cards_enabled', '0'), FILTER_VALIDATE_BOOLEAN)) {
            throw new GiftCardPurchaseException('Gift card purchases are temporarily unavailable.');
        }
        $rate = GiftCardRate::query()->whereKey($rateId)->where('is_active', true)
            ->whereIn('country', ['US', 'UK'])->where('sell_rate', '>', 0)
            ->where('usd_exchange_rate', '>', 0)->first();
        if (!$rate) throw new GiftCardPurchaseException('That USA or UK gift card is no longer available. Refresh the gift card list and try again.');
        if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) throw new GiftCardPurchaseException('Enter a valid recipient email address.');

        $quote = $this->quote($rate, $faceValue);
        $reference = 'GC-' . now()->format('ymd') . '-' . Str::upper(Str::random(8));
        $token = $idempotencyToken ? substr(hash('sha256', $idempotencyToken), 0, 64) : null;

        try {
            $result = DB::transaction(function () use ($user, $rate, $faceValue, $recipientEmail, $pin, $note, $quote, $reference, $token): array {
                if (!filter_var(AdminSetting::getSetting('gift_cards_enabled', '0'), FILTER_VALIDATE_BOOLEAN)) {
                    throw new GiftCardPurchaseException('Gift card purchases are temporarily unavailable.');
                }
                if ($token && Schema::hasColumn('gift_card_orders', 'bot_purchase_token')) {
                    $existing = GiftCardOrder::query()->where('bot_purchase_token', $token)
                        ->where('user_id', $user->id)->lockForUpdate()->first();
                    if ($existing) return ['order' => $existing, 'already_processed' => true];
                }

                $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);
                $perOrderLimit = (float) AdminSetting::getSetting('gift_card_purchase_per_order_limit_ngn', 250000);
                $dailyLimit = (float) AdminSetting::getSetting('gift_card_purchase_daily_limit_ngn', 500000);
                if ($quote['total_ngn'] > $perOrderLimit) {
                    return ['error' => 'The maximum gift card purchase per order is ₦' . number_format($perOrderLimit, 2) . '.'];
                }
                $spentToday = (float) GiftCardOrder::where('user_id', $lockedUser->id)->whereDate('created_at', today())
                    ->whereIn('status', ['pending', 'completed'])->sum('total_ngn');
                if ($spentToday + $quote['total_ngn'] > $dailyLimit) {
                    $remaining = max(0, $dailyLimit - $spentToday);
                    return ['error' => 'Your remaining gift card purchase limit today is ₦' . number_format($remaining, 2) . '.'];
                }
                if ($lockedUser->pin_locked_until && Carbon::now()->lt($lockedUser->pin_locked_until)) {
                    $minutes = max(1, (int) ceil(Carbon::now()->diffInSeconds($lockedUser->pin_locked_until) / 60));
                    return ['error' => "PIN locked. Try again in {$minutes} minute(s)."];
                }
                if (!$lockedUser->transaction_pin || !Hash::check($pin, $lockedUser->transaction_pin)) {
                    $lockedUser->pin_attempts = (int) ($lockedUser->pin_attempts ?? 0) + 1;
                    if ($lockedUser->pin_attempts >= 5) $lockedUser->pin_locked_until = Carbon::now()->addMinutes(15);
                    $lockedUser->save();
                    return ['error' => 'Incorrect transaction PIN.'];
                }

                $lockedUser->pin_attempts = 0;
                $lockedUser->pin_locked_until = null;
                $lockedUser->save();
                if ((float) $lockedUser->balance < $quote['total_ngn']) {
                    throw new GiftCardPurchaseException('Your Naira wallet does not have enough funds for this gift card.');
                }

                $orderAttributes = [
                    'user_id' => $lockedUser->id, 'gift_card_rate_id' => $rate->id, 'reference' => $reference,
                    'card_name' => $rate->name, 'country' => $rate->country, 'currency' => $rate->currency,
                    'face_value' => $faceValue, 'unit_rate_ngn' => $rate->sell_rate,
                    'total_ngn' => $quote['total_ngn'], 'usd_exchange_rate' => $rate->usd_exchange_rate,
                    'base_total_ngn' => $quote['base_total_ngn'], 'purchase_markup_ngn' => $quote['service_fee_ngn'],
                    'usd_face_value' => $quote['usd_face_value'], 'effective_ngn_per_usd' => $quote['ngn_per_usd'],
                    'recipient_email' => $recipientEmail, 'customer_note' => $note,
                    'status' => 'pending',
                ];
                if ($token && Schema::hasColumn('gift_card_orders', 'bot_purchase_token')) {
                    $orderAttributes['bot_purchase_token'] = $token;
                }
                $order = GiftCardOrder::create($orderAttributes);
                app(BalanceSettlementService::class)->adjustNgnBalance((int) $lockedUser->id, -$quote['total_ngn'], 'gift_card.purchase', $reference);
                return ['order' => $order, 'already_processed' => false];
            });
        } catch (QueryException $exception) {
            // A duplicate Telegram confirmation is rejected by the unique token index.
            if (!$token) throw $exception;
            $existing = Schema::hasColumn('gift_card_orders', 'bot_purchase_token')
                ? GiftCardOrder::query()->where('bot_purchase_token', $token)->where('user_id', $user->id)->first()
                : null;
            if (!$existing) throw $exception;
            return $existing;
        }

        if (isset($result['error'])) throw new GiftCardPurchaseException($result['error']);
        $order = $result['order'];
        if (!$result['already_processed']) {
            foreach (User::where('is_admin', true)->pluck('id') as $adminId) {
                Notification::create([
                    'admin_id' => $adminId, 'type' => 'info', 'title' => 'Gift card purchase needs fulfillment',
                    'message' => $order->reference . ' · ' . $order->card_name . ' ' . $order->country . ' · ₦' . number_format((float) $order->total_ngn, 2),
                    'data' => ['type' => 'gift_card_order', 'order_id' => $order->id], 'is_read' => false,
                ]);
            }
        }
        return $order;
    }
}
