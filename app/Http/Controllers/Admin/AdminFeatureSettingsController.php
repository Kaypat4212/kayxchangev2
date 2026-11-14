<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use Illuminate\Http\Request;

class AdminFeatureSettingsController extends Controller
{
    public function index()
    {
        return view('admin.features.index', [
            'billPaymentsEnabled' => filter_var(AdminSetting::getSetting('bill_payments_enabled', '1'), FILTER_VALIDATE_BOOLEAN),
            'utilityPaymentsEnabled' => filter_var(AdminSetting::getSetting('utility_payments_enabled', '0'), FILTER_VALIDATE_BOOLEAN),
            'billAdminReviewEnabled' => filter_var(AdminSetting::getSetting('bill_admin_review_enabled', '1'), FILTER_VALIDATE_BOOLEAN),
            'adminPresenceActive' => filter_var(AdminSetting::getSetting('admin_presence_active', '1'), FILTER_VALIDATE_BOOLEAN),
            'aiChatbotEnabled' => filter_var(AdminSetting::getSetting('ai_chatbot_enabled', '1'), FILTER_VALIDATE_BOOLEAN),
            'cryptoDepositsEnabled' => filter_var(AdminSetting::getSetting('nowpayments_enabled', '0'), FILTER_VALIDATE_BOOLEAN),
            'cryptoDepositReviewEnabled' => filter_var(AdminSetting::getSetting('crypto_deposit_admin_review_enabled', '1'), FILTER_VALIDATE_BOOLEAN),
            'cryptoWithdrawalMaxUsd' => AdminSetting::getSetting('crypto_withdrawal_max_usd', '5000'),
            'cryptoWithdrawalDailyUsd' => AdminSetting::getSetting('crypto_withdrawal_daily_usd', '10000'),
        ]);
    }

    public function update(Request $request)
    {
        $limits = $request->validate([
            'crypto_withdrawal_max_usd' => 'required|numeric|min:100|max:1000000',
            'crypto_withdrawal_daily_usd' => 'required|numeric|min:100|max:5000000',
        ]);
        AdminSetting::set('bill_payments_enabled', $request->boolean('bill_payments_enabled') ? '1' : '0');
        AdminSetting::set('utility_payments_enabled', $request->boolean('utility_payments_enabled') ? '1' : '0');
        AdminSetting::set('bill_admin_review_enabled', $request->boolean('bill_admin_review_enabled') ? '1' : '0');
        AdminSetting::set('admin_presence_active', $request->boolean('admin_presence_active') ? '1' : '0');
        AdminSetting::set('ai_chatbot_enabled', $request->boolean('ai_chatbot_enabled') ? '1' : '0');
        AdminSetting::set('nowpayments_enabled', $request->boolean('crypto_deposits_enabled') ? '1' : '0');
        AdminSetting::set('crypto_deposit_admin_review_enabled', $request->boolean('crypto_deposit_review_enabled') ? '1' : '0');
        AdminSetting::set('crypto_withdrawal_max_usd', (string) $limits['crypto_withdrawal_max_usd']);
        AdminSetting::set('crypto_withdrawal_daily_usd', (string) $limits['crypto_withdrawal_daily_usd']);

        return back()->with('success', 'Platform feature controls and admin availability saved.');
    }

    public function togglePresence()
    {
        $active = filter_var(AdminSetting::getSetting('admin_presence_active', '1'), FILTER_VALIDATE_BOOLEAN);
        AdminSetting::set('admin_presence_active', $active ? '0' : '1');
        return back()->with('success', $active ? 'Admin status set to away.' : 'Admin status set to active.');
    }
}
