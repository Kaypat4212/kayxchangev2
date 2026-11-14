<?php

namespace App\Http\Controllers;

use App\Models\CryptoRate;
use Illuminate\Http\Request;

class RateCalculatorController extends Controller
{
    /**
     * Show the rate calculator page.
     */
    public function index()
    {
        $coins = CryptoRate::query()->pluck('coin')->map(fn ($coin) => strtoupper((string) $coin))->merge(['BTC', 'ETH', 'USDT', 'SOL', 'BNB', 'USDC', 'LTC', 'ADA', 'DOGE'])->unique()->values()->all();
        $rates = app(\App\Services\CryptoTradeRateService::class)->rates($coins);
        return view('rates.calculator', compact('rates'));
    }

    /**
     * AJAX: return the current rates as JSON (for live refresh).
     */
    public function apiRates()
    {
        $coins = CryptoRate::query()->pluck('coin')->map(fn ($coin) => strtoupper((string) $coin))->merge(['BTC', 'ETH', 'USDT', 'SOL', 'BNB', 'USDC', 'LTC', 'ADA', 'DOGE'])->unique()->values()->all();
        $rates = app(\App\Services\CryptoTradeRateService::class)->rates($coins);
        return response()->json($rates);
    }
}
