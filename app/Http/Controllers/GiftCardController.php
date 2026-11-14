<?php

namespace App\Http\Controllers;

use App\Models\AdminSetting;
use App\Models\GiftCardOrder;
use App\Models\GiftCardRate;
use App\Models\GiftCardSaleOrder;
use App\Services\BalanceSettlementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use RuntimeException;

class GiftCardController extends Controller
{
    public function index()
    {
        $enabled = filter_var(AdminSetting::getSetting('gift_cards_enabled', '0'), FILTER_VALIDATE_BOOLEAN);
        GiftCardRate::firstOrCreate([
            'name' => 'iTunes / Apple', 'country' => 'US', 'currency' => 'USD',
        ], [
            'category' => 'gaming', 'buy_rate' => 0, 'sell_rate' => 1590,
            'purchase_markup_ngn' => 3000, 'usd_exchange_rate' => 1, 'is_active' => true,
        ]);
        $rates = GiftCardRate::query()->where('is_active', true)->whereIn('country', ['US', 'UK'])
            ->where('sell_rate', '>', 0)->where('usd_exchange_rate', '>', 0)->orderBy('category')->orderBy('name')->get();
        $orders = GiftCardOrder::where('user_id', auth()->id())->latest()->paginate(10);
        $salesEnabled = filter_var(AdminSetting::getSetting('gift_card_sales_enabled', '0'), FILTER_VALIDATE_BOOLEAN);
        $saleRates = GiftCardRate::query()->where('is_active', true)->whereIn('country', ['US', 'UK'])
            ->where('buy_rate', '>', 0)->orderBy('category')->orderBy('name')->get();
        $saleOrders = GiftCardSaleOrder::where('user_id', auth()->id())->latest()->paginate(10, ['*'], 'sales_page');
        $purchasePerOrderLimit = (float) AdminSetting::getSetting('gift_card_purchase_per_order_limit_ngn', 250000);
        $purchaseDailyLimit = (float) AdminSetting::getSetting('gift_card_purchase_daily_limit_ngn', 500000);
        $purchaseSpentToday = (float) GiftCardOrder::where('user_id', auth()->id())->whereDate('created_at', today())->whereIn('status', ['pending', 'completed'])->sum('total_ngn');
        $purchaseDailyRemaining = max(0, $purchaseDailyLimit - $purchaseSpentToday);
        return view('gift-cards.index', compact('enabled', 'rates', 'orders', 'salesEnabled', 'saleRates', 'saleOrders', 'purchasePerOrderLimit', 'purchaseDailyLimit', 'purchaseDailyRemaining'));
    }

    public function storeSale(Request $request)
    {
        abort_unless(filter_var(AdminSetting::getSetting('gift_card_sales_enabled', '0'), FILTER_VALIDATE_BOOLEAN), 404);
        $data = $request->validate([
            'gift_card_rate_id' => ['required', 'integer', 'exists:gift_card_rates,id'],
            'face_value' => ['required', 'numeric', 'min:1', 'max:10000'],
            'card_code' => ['required', 'string', 'min:4', 'max:2000'],
            'card_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'customer_note' => ['nullable', 'string', 'max:500'],
        ]);
        $rate = GiftCardRate::query()->whereKey($data['gift_card_rate_id'])->where('is_active', true)
            ->whereIn('country', ['US', 'UK'])->where('buy_rate', '>', 0)->firstOrFail();
        $reference = 'GCS-' . now()->format('ymd') . '-' . Str::upper(Str::random(8));
        $imagePath = $request->hasFile('card_image')
            ? $request->file('card_image')->store('gift-card-sales', 'local')
            : null;

        try {
            $order = DB::transaction(function () use ($request, $rate, $data, $reference, $imagePath) {
                if (!filter_var(AdminSetting::getSetting('gift_card_sales_enabled', '0'), FILTER_VALIDATE_BOOLEAN)) abort(404);
                return GiftCardSaleOrder::create([
                    'user_id' => $request->user()->id, 'gift_card_rate_id' => $rate->id, 'reference' => $reference,
                    'card_name' => $rate->name, 'country' => $rate->country, 'currency' => $rate->currency,
                    'face_value' => $data['face_value'], 'unit_rate_ngn' => $rate->buy_rate,
                    'estimated_ngn' => round((float) $data['face_value'] * (float) $rate->buy_rate, 2),
                    'card_code' => $data['card_code'], 'image_path' => $imagePath,
                    'customer_note' => $data['customer_note'] ?? null, 'status' => 'pending',
                ]);
            });
        } catch (\Throwable $exception) {
            if ($imagePath) \Illuminate\Support\Facades\Storage::disk('local')->delete($imagePath);
            throw $exception;
        }

        foreach (\App\Models\User::where('is_admin', true)->pluck('id') as $adminId) {
            \App\Models\Notification::create([
                'admin_id' => $adminId, 'type' => 'warning', 'title' => 'Gift card sale needs review',
                'message' => $order->reference . ' · ' . $order->card_name . ' ' . $order->country . ' · estimated ₦' . number_format((float) $order->estimated_ngn, 2),
                'data' => ['type' => 'gift_card_sale', 'order_id' => $order->id], 'is_read' => false,
            ]);
        }
        return redirect()->route('gift-cards.index')->with('success', 'Gift card sale ' . $order->reference . ' was submitted for review. No balance was credited yet.');
    }

    public function store(Request $request)
    {
        abort_unless(filter_var(AdminSetting::getSetting('gift_cards_enabled', '0'), FILTER_VALIDATE_BOOLEAN), 404);
        $data = $request->validate([
            'gift_card_rate_id' => ['required', 'integer', 'exists:gift_card_rates,id'],
            'face_value' => ['required', 'numeric', 'min:1', 'max:10000'],
            'recipient_email' => ['required', 'email:rfc', 'max:190'],
            'customer_note' => ['nullable', 'string', 'max:500'],
            'pin' => ['required', 'digits:4'],
        ]);

        $rate = GiftCardRate::query()->whereKey($data['gift_card_rate_id'])->where('is_active', true)
            ->whereIn('country', ['US', 'UK'])->where('sell_rate', '>', 0)
            ->where('usd_exchange_rate', '>', 0)->firstOrFail();
        $baseAmount = round((float) $data['face_value'] * (float) $rate->sell_rate, 2);
        $purchaseMarkup = max(0, (float) ($rate->purchase_markup_ngn ?? 3000));
        $amount = round($baseAmount + $purchaseMarkup, 2);
        $usdExchangeRate = (float) $rate->usd_exchange_rate;
        $usdFaceValue = round((float) $data['face_value'] * $usdExchangeRate, 2);
        $ngnPerUsd = round((float) $rate->sell_rate / $usdExchangeRate, 2);
        $reference = 'GC-' . now()->format('ymd') . '-' . Str::upper(Str::random(8));

        try {
            $result = DB::transaction(function () use ($request, $rate, $data, $amount, $baseAmount, $purchaseMarkup, $usdExchangeRate, $usdFaceValue, $ngnPerUsd, $reference) {
                if (!filter_var(AdminSetting::getSetting('gift_cards_enabled', '0'), FILTER_VALIDATE_BOOLEAN)) {
                    abort(404);
                }
                $user = $request->user()->newQuery()->lockForUpdate()->findOrFail($request->user()->id);
                $perOrderLimit = (float) AdminSetting::getSetting('gift_card_purchase_per_order_limit_ngn', 250000);
                $dailyLimit = (float) AdminSetting::getSetting('gift_card_purchase_daily_limit_ngn', 500000);
                if ($amount > $perOrderLimit) {
                    return ['error' => 'The maximum gift card purchase per order is ₦' . number_format($perOrderLimit, 2) . '.'];
                }
                $spentToday = (float) GiftCardOrder::where('user_id', $user->id)->whereDate('created_at', today())
                    ->whereIn('status', ['pending', 'completed'])->sum('total_ngn');
                if ($spentToday + $amount > $dailyLimit) {
                    $remaining = max(0, $dailyLimit - $spentToday);
                    return ['error' => 'Your remaining gift card purchase limit today is ₦' . number_format($remaining, 2) . '.'];
                }
                if ($user->pin_locked_until && Carbon::now()->lt($user->pin_locked_until)) {
                    $minutes = max(1, (int) ceil(Carbon::now()->diffInSeconds($user->pin_locked_until) / 60));
                    return ['error' => "PIN locked. Try again in {$minutes} minute(s)."];
                }
                if (!$user->transaction_pin || !Hash::check($data['pin'], $user->transaction_pin)) {
                    $user->pin_attempts = (int) ($user->pin_attempts ?? 0) + 1;
                    if ($user->pin_attempts >= 5) $user->pin_locked_until = Carbon::now()->addMinutes(15);
                    $user->save();
                    return ['error' => 'Incorrect transaction PIN.'];
                }
                $user->pin_attempts = 0;
                $user->pin_locked_until = null;
                $user->save();
                if ((float) $user->balance < $amount) {
                    throw new RuntimeException('Your Naira wallet does not have enough funds for this gift card.');
                }
                $order = GiftCardOrder::create([
                    'user_id' => $user->id, 'gift_card_rate_id' => $rate->id, 'reference' => $reference,
                    'card_name' => $rate->name, 'country' => $rate->country, 'currency' => $rate->currency,
                    'face_value' => $data['face_value'], 'unit_rate_ngn' => $rate->sell_rate,
                    'total_ngn' => $amount, 'usd_exchange_rate' => $usdExchangeRate,
                    'base_total_ngn' => $baseAmount, 'purchase_markup_ngn' => $purchaseMarkup,
                    'usd_face_value' => $usdFaceValue, 'effective_ngn_per_usd' => $ngnPerUsd,
                    'recipient_email' => $data['recipient_email'],
                    'customer_note' => $data['customer_note'] ?? null, 'status' => 'pending',
                ]);
                app(BalanceSettlementService::class)->adjustNgnBalance((int) $user->id, -$amount, 'gift_card.purchase', $reference);
                return ['order' => $order];
            });
            if (isset($result['error'])) return back()->withInput()->withErrors(['purchase' => $result['error']]);
            $order = $result['order'];
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors(['purchase' => $e->getMessage()]);
        }

        foreach (\App\Models\User::where('is_admin', true)->pluck('id') as $adminId) {
            \App\Models\Notification::create([
                'admin_id' => $adminId,
                'type' => 'info',
                'title' => 'Gift card purchase needs fulfillment',
                'message' => $order->reference . ' · ' . $order->card_name . ' ' . $order->country . ' · ₦' . number_format($amount, 2),
                'data' => ['type' => 'gift_card_order', 'order_id' => $order->id],
                'is_read' => false,
            ]);
        }

        return redirect()->route('gift-cards.index')->with('success', 'Purchase request ' . $order->reference . ' is in the fulfillment queue. ₦' . number_format($amount, 2) . ' was reserved from your Naira wallet.');
    }
}
