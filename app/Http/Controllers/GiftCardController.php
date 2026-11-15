<?php

namespace App\Http\Controllers;

use App\Models\AdminSetting;
use App\Models\GiftCardRate;
use App\Models\GiftCardSaleOrder;
use App\Exceptions\GiftCardPurchaseException;
use App\Models\GiftCardOrder;
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
        $data = $request->validate([
            'gift_card_rate_id' => ['required', 'integer', 'exists:gift_card_rates,id'],
            'face_value' => ['required', 'numeric', 'min:1', 'max:10000'],
            'recipient_email' => ['required', 'email:rfc', 'max:190'],
            'customer_note' => ['nullable', 'string', 'max:500'],
            'pin' => ['required', 'digits:4'],
        ]);

        try {
            $order = app(\App\Services\GiftCardPurchaseService::class)->purchase(
                $request->user(), (int) $data['gift_card_rate_id'], (float) $data['face_value'],
                $data['recipient_email'], $data['pin'], $data['customer_note'] ?? null
            );
        } catch (GiftCardPurchaseException $e) {
            return back()->withInput()->withErrors(['purchase' => $e->getMessage()]);
        }
        return redirect()->route('gift-cards.index')->with('success', 'Purchase request ' . $order->reference . ' is in the fulfillment queue. ₦' . number_format((float) $order->total_ngn, 2) . ' was reserved from your Naira wallet.');
    }
}
