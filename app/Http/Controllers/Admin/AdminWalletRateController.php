<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use Illuminate\Http\Request;

class AdminWalletRateController extends Controller
{
    public function index(\App\Services\CryptoTradeRateService $tradeRates)
    {
        $usdtRates = $tradeRates->rates(['USDT'])['USDT'] ?? ['buy_rate' => null, 'sell_rate' => null];
        return view('admin.wallet-rates', [
            'buyRate' => AdminSetting::getSetting('usd_wallet_buy_rate_ngn', ''),
            'sellRate' => AdminSetting::getSetting('usd_wallet_sell_rate_ngn', ''),
            'usdtBuyRate' => $usdtRates['buy_rate'],
            'usdtSellRate' => $usdtRates['sell_rate'],
        ]);
    }

    public function syncFromUsdt(\App\Services\CryptoTradeRateService $tradeRates)
    {
        $rates = $tradeRates->rates(['USDT'])['USDT'] ?? [];
        $buy = (float) ($rates['buy_rate'] ?? 0);
        $sell = (float) ($rates['sell_rate'] ?? 0);
        if ($buy <= 0 || $sell <= 0) {
            return back()->withErrors(['rate_sync' => 'Platform USDT rates are unavailable. Configure the USDT buy and sell rates before syncing.']);
        }

        AdminSetting::set('usd_wallet_buy_rate_ngn', number_format($buy, 2, '.', ''));
        AdminSetting::set('usd_wallet_sell_rate_ngn', number_format($sell, 2, '.', ''));

        return back()->with('success', 'USD wallet buy and sell rates now match the platform USDT rates.');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'buy_rate' => 'required|numeric|min:1|max:10000000',
            'sell_rate' => 'required|numeric|min:1|max:10000000',
        ]);

        AdminSetting::set('usd_wallet_buy_rate_ngn', number_format((float) $data['buy_rate'], 2, '.', ''));
        AdminSetting::set('usd_wallet_sell_rate_ngn', number_format((float) $data['sell_rate'], 2, '.', ''));

        return back()->with('success', 'USD wallet rates updated.');
    }
}
