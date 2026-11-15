<?php

namespace App\Services;

use App\Exceptions\GiftCardPurchaseException;
use App\Models\AdminSetting;
use App\Models\GiftCardOrder;
use App\Models\GiftCardRate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use Throwable;

class KayBotGiftCardFlow
{
    public function begin(Request $request, ?User $user, string $message): array
    {
        if (!$user) return ['reply' => 'Please sign in to submit a gift-card request.'];
        $kind = preg_match('/\b(sell|selling|trade|trading)\b/i', $message) ? 'sell' : 'purchase';
        $toggle = $kind === 'purchase' ? 'gift_cards_enabled' : 'gift_card_sales_enabled';
        if (!filter_var(AdminSetting::getSetting($toggle, '0'), FILTER_VALIDATE_BOOLEAN)) {
            return ['reply' => $kind === 'purchase' ? 'Gift-card purchases are currently paused.' : 'Gift-card selling is currently paused.'];
        }
        $state = ['kind' => $kind, 'step' => 'rate', 'token' => (string) Str::uuid()];
        $request->session()->put('kaybot_giftcard', $state);
        return $this->ratePrompt($request, $state);
    }

    public function handle(Request $request, ?User $user, string $message, array $state): array
    {
        $lower = mb_strtolower(trim($message));
        if (preg_match('/\b(cancel|stop|abort|never mind)\b/i', $message)) {
            $request->session()->forget('kaybot_giftcard');
            return ['reply' => 'Gift-card request cancelled. Nothing was charged or submitted.', 'giftcard_done' => true];
        }
        if (!$user) {
            $request->session()->forget('kaybot_giftcard');
            return ['reply' => 'Please sign in again before continuing.'];
        }
        $kind = $state['kind'] ?? 'purchase';
        $toggle = $kind === 'purchase' ? 'gift_cards_enabled' : 'gift_card_sales_enabled';
        if (!filter_var(AdminSetting::getSetting($toggle, '0'), FILTER_VALIDATE_BOOLEAN)) {
            $request->session()->forget('kaybot_giftcard');
            return ['reply' => 'This gift-card feature has been paused. Your request was not submitted.'];
        }

        if (($state['step'] ?? '') === 'rate') {
            $rates = $this->rates($kind);
            $rate = $this->selectRate($message, $rates);
            if (!$rate) return $this->ratePrompt($request, $state);
            $state['rate_id'] = $rate->id;
            $state['step'] = 'face_value';
            $request->session()->put('kaybot_giftcard', $state);
            if ($kind === 'purchase' && GiftCardPurchaseService::isDenominationCard($rate)) {
                return $this->denominationPrompt($rate);
            }
            $rateText = $kind === 'purchase'
                ? 'Rate: ₦' . number_format((float)$rate->sell_rate, 2) . ' per ' . $rate->currency . ', plus service fee.'
                : 'Estimated rate: ₦' . number_format((float)$rate->buy_rate, 2) . ' per ' . $rate->currency . '; final payout follows admin review.';
            return ['reply' => "Selected {$rate->name} · {$rate->country}. {$rateText}\nEnter its face value (1–10,000 {$rate->currency}).", 'giftcard_step' => 'face_value'];
        }

        $rate = GiftCardRate::query()->whereKey($state['rate_id'] ?? 0)->where('is_active', true)->whereIn('country', ['US','UK'])->first();
        if (!$rate) {
            $request->session()->forget('kaybot_giftcard');
            return ['reply' => 'That card rate is no longer available. Start again with “buy gift card” or “sell gift card”.'];
        }
        if (($state['step'] ?? '') === 'face_value') {
            $value = is_numeric(str_replace(',', '', $message)) ? (float)str_replace(',', '', $message) : 0;
            $unitRate = $kind === 'purchase' ? (float)$rate->sell_rate : (float)$rate->buy_rate;
            if ($value < 1 || $value > 10000 || $unitRate <= 0) return ['reply' => 'Enter a valid face value from 1 to 10,000.'];
            if ($kind === 'purchase' && !GiftCardPurchaseService::isValidDenomination($rate, $value)) {
                return $this->denominationPrompt($rate, 'Please choose one of the listed denominations:');
            }
            $state['face_value'] = round($value, 2);
            $state['step'] = $kind === 'purchase' ? 'recipient' : 'sale_code';
            $request->session()->put('kaybot_giftcard', $state);
            return $kind === 'purchase'
                ? ['reply' => 'What email address should receive the digital card?', 'giftcard_step' => 'recipient']
                : ['reply' => 'Enter the gift-card code for admin verification. It is stored encrypted; your wallet is credited only after approval.', 'giftcard_step' => 'sale_code', 'sensitive_input_next' => true];
        }

        if (($state['step'] ?? '') === 'recipient') {
            if (!filter_var(trim($message), FILTER_VALIDATE_EMAIL)) return ['reply' => 'Please enter a valid recipient email address.'];
            $state['recipient'] = trim($message);
            $state['step'] = 'purchase_note';
            $request->session()->put('kaybot_giftcard', $state);
            return ['reply' => 'Add an optional note for the fulfillment team, or type skip.'];
        }
        if (($state['step'] ?? '') === 'purchase_note') {
            $state['note'] = in_array($lower, ['skip','none','no'], true) ? null : mb_substr(trim($message), 0, 500);
            $quote = app(GiftCardPurchaseService::class)->quote($rate, (float)$state['face_value']);
            $state['expected_total_ngn'] = (float) $quote['total_ngn'];
            $state['step'] = 'purchase_review';
            $request->session()->put('kaybot_giftcard', $state);
            return ['reply' => "Review purchase\n{$rate->name} · {$rate->country}\nFace value: {$rate->currency} " . number_format($state['face_value'],2) . "\nRate: ₦" . number_format($quote['ngn_per_usd'],2) . "/USD\nService fee: ₦" . number_format($quote['service_fee_ngn'],2) . "\nTotal: ₦" . number_format($quote['total_ngn'],2) . "\nRecipient: {$state['recipient']}\n\nConfirm to enter your transaction PIN. Your Naira wallet is charged only after PIN verification.", 'giftcard_step' => 'purchase_review', 'quick_replies' => ['✅ Confirm purchase','❌ Cancel']];
        }
        if (($state['step'] ?? '') === 'purchase_review') {
            if (!preg_match('/\b(confirm|yes)\b/i', $message)) return ['reply' => 'Choose Confirm purchase or Cancel.', 'quick_replies' => ['✅ Confirm purchase','❌ Cancel']];
            $state['step'] = 'purchase_pin';
            $request->session()->put('kaybot_giftcard', $state);
            return ['reply' => 'Enter your 4-digit transaction PIN. Do not share it with anyone; KayBot will not save it in chat history.', 'giftcard_step' => 'purchase_pin', 'sensitive_input_next' => true];
        }
        if (($state['step'] ?? '') === 'purchase_pin') {
            if (!preg_match('/^\d{4}$/', trim($message))) return ['reply' => 'Enter your 4-digit transaction PIN or type cancel.', 'giftcard_step' => 'purchase_pin', 'sensitive_input_next' => true];
            try {
                $order = app(GiftCardPurchaseService::class)->purchase($user, (int)$rate->id, (float)$state['face_value'], (string)$state['recipient'], trim($message), $state['note'] ?? null, (string)$state['token'], (float)($state['expected_total_ngn'] ?? 0));
                $request->session()->forget('kaybot_giftcard');
                return ['reply' => "Gift-card request received. Ref {$order->reference}. ₦" . number_format((float)$order->total_ngn,2) . " was reserved from your Naira wallet. It is pending admin fulfillment.", 'giftcard_done' => true];
            } catch (GiftCardPurchaseException $e) {
                if (str_contains(mb_strtolower($e->getMessage()), 'pin')) return ['reply' => $e->getMessage() . ' Try again or type cancel.', 'giftcard_step' => 'purchase_pin', 'sensitive_input_next' => true];
                $request->session()->forget('kaybot_giftcard');
                return ['reply' => $e->getMessage(), 'giftcard_done' => true];
            } catch (Throwable $e) {
                report($e);
                $request->session()->forget('kaybot_giftcard');
                return ['reply' => 'We could not submit this purchase. Check Gift Cards → Purchase history before retrying, or contact support.', 'giftcard_done' => true];
            }
        }

        if (($state['step'] ?? '') === 'sale_code') {
            $state['card_code'] = Crypt::encryptString(trim($message));
            $state['step'] = 'sale_note';
            $request->session()->put('kaybot_giftcard', $state);
            return ['reply' => 'Add an optional note for card verification or type skip. The code is excluded from chat history.', 'giftcard_step' => 'sale_note', 'sensitive_input' => true];
        }
        if (($state['step'] ?? '') === 'sale_note') {
            $state['note'] = in_array($lower, ['skip','none','no'], true) ? null : mb_substr(trim($message), 0, 500);
            $state['step'] = 'sale_review';
            $request->session()->put('kaybot_giftcard', $state);
            $estimate = (float)$state['face_value'] * (float)$rate->buy_rate;
            return ['reply' => "Review sale\n{$rate->name} · {$rate->country}\nFace value: {$rate->currency} " . number_format($state['face_value'],2) . "\nEstimated at admin buy rate: ₦" . number_format($estimate,2) . "\n\nAdmin verifies the card before approval. No wallet credit is made until then. Submit for review?", 'giftcard_step' => 'sale_review', 'quick_replies' => ['✅ Submit for review','❌ Cancel']];
        }
        if (($state['step'] ?? '') === 'sale_review') {
            if (!preg_match('/\b(submit|confirm|yes)\b/i', $message)) return ['reply' => 'Choose Submit for review or Cancel.', 'quick_replies' => ['✅ Submit for review','❌ Cancel']];
            try {
                $cardCode = Crypt::decryptString((string)($state['card_code'] ?? ''));
                $order = app(GiftCardSaleService::class)->submit($user, (int)$rate->id, (float)$state['face_value'], $cardCode, $state['note'] ?? null, (string)$state['token']);
                $request->session()->forget('kaybot_giftcard');
                return ['reply' => "Gift-card sale sent to admin for verification. Ref {$order->reference}. Estimated value ₦" . number_format((float)$order->estimated_ngn,2) . ". The final NGN credit requires admin approval.", 'giftcard_done' => true];
            } catch (Throwable $e) {
                report($e);
                $request->session()->forget('kaybot_giftcard');
                return ['reply' => $e->getMessage(), 'giftcard_done' => true];
            }
        }
        $request->session()->forget('kaybot_giftcard');
        return ['reply' => 'This gift-card chat expired. Start again with “buy gift card” or “sell gift card”.'];
    }

    private function rates(string $kind)
    {
        $query = GiftCardRate::query()->where('is_active', true)->whereIn('country', ['US','UK']);
        $query->where($kind === 'purchase' ? 'sell_rate' : 'buy_rate', '>', 0);
        if ($kind === 'purchase') $query->where('usd_exchange_rate', '>', 0);
        return $query->orderBy('country')->orderBy('name')->limit(20)->get();
    }

    private function ratePrompt(Request $request, array $state): array
    {
        $kind = $state['kind'] ?? 'purchase';
        $rates = $this->rates($kind);
        if ($rates->isEmpty()) {
            $request->session()->forget('kaybot_giftcard');
            return ['reply' => 'No active USA/UK rates are available for this gift-card transaction.'];
        }
        $list = [];
        foreach ($rates as $index => $rate) {
            $unit = $kind === 'purchase' ? (float)$rate->sell_rate : (float)$rate->buy_rate;
            $list[] = ($index + 1) . '. ' . $rate->name . ' · ' . $rate->country . ' · ' . $rate->currency . ' · ₦' . number_format($unit,2) . '/' . $rate->currency;
        }
        $state['rate_ids'] = $rates->pluck('id')->all();
        $request->session()->put('kaybot_giftcard', $state);
        return ['reply' => ($kind === 'purchase' ? 'Choose a gift card to buy:' : 'Choose the gift card you want to sell:') . "\n" . implode("\n", $list) . "\nReply with its number or name, or type cancel.", 'giftcard_step' => 'rate'];
    }

    private function selectRate(string $message, $rates): ?GiftCardRate
    {
        if (preg_match('/^(\d{1,2})\b/', trim($message), $match)) return $rates->values()->get(((int)$match[1]) - 1);
        $needle = mb_strtolower(trim($message));
        foreach ($rates as $rate) {
            if ($needle !== '' && (str_contains($needle, mb_strtolower($rate->name)) || str_contains(mb_strtolower($rate->name), $needle))) return $rate;
        }
        return null;
    }

    private function denominationPrompt(GiftCardRate $rate, string $lead = 'Choose one of these USA Apple/iTunes denominations:'): array
    {
        $purchaseService = app(GiftCardPurchaseService::class);
        $choices = [];
        foreach ($purchaseService->denominationsFor($rate) as $amount) {
            $quote = $purchaseService->quote($rate, (float) $amount);
            $choices[] = '$' . $amount . ' = ₦' . number_format($quote['base_total_ngn'], 0);
        }
        return [
            'reply' => $lead . "\n" . implode(' · ', $choices)
                . "\nRate: ₦" . number_format($purchaseService->quote($rate, 2)['ngn_per_usd'], 2) . '/USD'
                . ' · Admin service fee: ₦' . number_format((float) ($rate->purchase_markup_ngn ?? 0), 2)
                . "\nReply with the USD amount. The next step confirms the exact wallet total.",
            'giftcard_step' => 'face_value',
        ];
    }
}
