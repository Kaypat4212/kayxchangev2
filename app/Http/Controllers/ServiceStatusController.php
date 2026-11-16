<?php

namespace App\Http\Controllers;

use App\Models\AdminSetting;

class ServiceStatusController extends Controller
{
    public function index()
    {
        $maintenance = AdminSetting::getSetting('runtime_mode', 'production') === 'off'
            || filter_var(AdminSetting::getSetting('site_maintenance', '0'), FILTER_VALIDATE_BOOLEAN);

        $services = collect([
            ['name' => 'Naira deposits', 'icon' => 'bi-bank', 'enabled' => true, 'note' => 'Bank and supported payment methods'],
            ['name' => 'Crypto deposits', 'icon' => 'bi-box-arrow-in-down', 'enabled' => AdminSetting::getSetting('nowpayments_enabled', '0') === '1', 'note' => 'Provider confirmation and wallet credit'],
            ['name' => 'Crypto swaps', 'icon' => 'bi-arrow-left-right', 'enabled' => AdminSetting::getSetting('cryptomus_convert_enabled', '1') === '1', 'note' => 'Swap availability and settlement review'],
            ['name' => 'Crypto withdrawals', 'icon' => 'bi-send', 'enabled' => AdminSetting::getSetting('nowpayments_crypto_withdrawals_enabled', '0') === '1', 'note' => 'Requests may require admin review'],
            ['name' => 'Bills and utilities', 'icon' => 'bi-lightning-charge', 'enabled' => filter_var(AdminSetting::getSetting('bill_payments_enabled', '1'), FILTER_VALIDATE_BOOLEAN), 'note' => 'Airtime, data, and supported billers'],
            ['name' => 'Gift card purchases', 'icon' => 'bi-gift', 'enabled' => filter_var(AdminSetting::getSetting('gift_cards_enabled', '0'), FILTER_VALIDATE_BOOLEAN), 'note' => 'Order fulfillment and delivery tracking'],
            ['name' => 'Gift card sales', 'icon' => 'bi-receipt', 'enabled' => filter_var(AdminSetting::getSetting('gift_card_sales_enabled', '0'), FILTER_VALIDATE_BOOLEAN), 'note' => 'Admin reviewed; availability may vary'],
        ])->map(function (array $service) use ($maintenance) {
            $service['state'] = $maintenance ? 'Maintenance' : ($service['enabled'] ? 'Enabled' : 'Paused');
            $service['available'] = !$maintenance && $service['enabled'];
            return $service;
        });

        $updatedAt = now();

        return view('status.index', compact('services', 'maintenance', 'updatedAt'));
    }
}
