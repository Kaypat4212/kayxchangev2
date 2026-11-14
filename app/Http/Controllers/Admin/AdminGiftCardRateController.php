<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GiftCardRate;
use App\Models\AdminSetting;
use App\Services\RateNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class AdminGiftCardRateController extends Controller
{
    public function index()
    {
        // Auto-seed defaults if table is empty
        if (GiftCardRate::count() === 0) {
            foreach (GiftCardRate::defaultCards() as $card) {
                GiftCardRate::create(array_merge($card, ['buy_rate' => 0, 'sell_rate' => 0, 'is_active' => true]));
            }
        }

        $rates = GiftCardRate::orderBy('category')->orderBy('name')->orderBy('country')->get();
        $grouped = $rates->groupBy('category');

        $giftCardsEnabled = filter_var(AdminSetting::getSetting('gift_cards_enabled', '0'), FILTER_VALIDATE_BOOLEAN);
        $giftCardSalesEnabled = filter_var(AdminSetting::getSetting('gift_card_sales_enabled', '0'), FILTER_VALIDATE_BOOLEAN);
        $purchasePerOrderLimit = (float) AdminSetting::getSetting('gift_card_purchase_per_order_limit_ngn', 250000);
        $purchaseDailyLimit = (float) AdminSetting::getSetting('gift_card_purchase_daily_limit_ngn', 500000);
        return view('admin.gift-card-rates.index', compact('grouped', 'rates', 'giftCardsEnabled', 'giftCardSalesEnabled', 'purchasePerOrderLimit', 'purchaseDailyLimit'));
    }

    public function toggleFeature(Request $request)
    {
        AdminSetting::set('gift_cards_enabled', $request->boolean('enabled') ? '1' : '0');
        return back()->with('success', $request->boolean('enabled')
            ? 'Gift card purchases are now enabled for active USA and UK cards.'
            : 'Gift card purchases are now paused. Existing order history and fulfillment remain available.');
    }

    public function toggleSales(Request $request)
    {
        AdminSetting::set('gift_card_sales_enabled', $request->boolean('enabled') ? '1' : '0');
        return back()->with('success', $request->boolean('enabled')
            ? 'Gift card sales are enabled. Users can submit USA and UK cards for admin review.'
            : 'Gift card sales are paused. Existing requests remain available for review.');
    }

    public function updatePurchaseLimits(Request $request)
    {
        $data = $request->validate([
            'per_order_limit_ngn' => ['required', 'numeric', 'min:1000', 'max:100000000'],
            'daily_limit_ngn' => ['required', 'numeric', 'min:1000', 'max:100000000'],
        ]);
        AdminSetting::set('gift_card_purchase_per_order_limit_ngn', (string) round((float) $data['per_order_limit_ngn'], 2));
        AdminSetting::set('gift_card_purchase_daily_limit_ngn', (string) round((float) $data['daily_limit_ngn'], 2));
        return back()->with('success', 'Gift card purchase limits updated. These limits apply to all users, without requiring KYC for card purchases.');
    }

    public function bulkUpdate(Request $request)
    {
        $data = $request->validate([
            'rates'              => 'required|array',
            'rates.*.buy_rate'   => 'required|numeric|min:0',
            'rates.*.sell_rate'  => 'required|numeric|min:0',
            'rates.*.is_active'  => 'nullable|boolean',
        ]);

        foreach ($request->input('rates') as $id => $row) {
            GiftCardRate::where('id', $id)->update([
                'buy_rate'  => $row['buy_rate'],
                'sell_rate' => $row['sell_rate'],
                'is_active' => isset($row['is_active']) ? 1 : 0,
            ]);
        }

        $this->dispatchRateNotifications();

        return back()->with('success', 'Gift card rates updated successfully.');
    }

    private function dispatchRateNotifications(): void
    {
        try {
            Artisan::queue('rates:notify', ['--context' => 'admin_update']);
        } catch (\Throwable $e) {
            Log::warning('Gift card rate notification dispatch failed: ' . $e->getMessage());
            try {
                app(RateNotificationService::class)->notifyAllUsers('admin_update');
            } catch (\Throwable $e2) {
                Log::warning('Gift card rate notification sync fallback failed: ' . $e2->getMessage());
            }
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'country'   => 'required|string|max:10',
            'currency'  => 'required|string|max:10',
            'category'  => 'required|string|max:50',
            'buy_rate'  => 'required|numeric|min:0',
            'sell_rate' => 'required|numeric|min:0',
        ]);

        GiftCardRate::create(array_merge($data, ['is_active' => true]));

        return back()->with('success', 'Gift card added successfully.');
    }

    public function update(Request $request, $id)
    {
        $rate = GiftCardRate::findOrFail($id);

        $data = $request->validate([
            'buy_rate'  => 'required|numeric|min:0',
            'sell_rate' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $rate->update([
            'buy_rate'  => $data['buy_rate'],
            'sell_rate' => $data['sell_rate'],
            'is_active' => isset($data['is_active']) ? 1 : 0,
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        GiftCardRate::findOrFail($id)->delete();
        return back()->with('success', 'Gift card deleted.');
    }
}
