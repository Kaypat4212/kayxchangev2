<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use Illuminate\Http\Request;

class AdminWalletRateController extends Controller
{
    public function index()
    {
        return view('admin.wallet-rates', [
            'buyRate' => AdminSetting::getSetting('usd_wallet_buy_rate_ngn', ''),
            'sellRate' => AdminSetting::getSetting('usd_wallet_sell_rate_ngn', ''),
        ]);
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
