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
        $coins = app(\App\Services\CryptoTradeRateService::class)->configuredCoins();
        $rates = app(\App\Services\CryptoTradeRateService::class)->rates($coins);
        return view('rates.calculator', compact('rates'));
    }

    /**
     * AJAX: return the current rates as JSON (for live refresh).
     */
    public function apiRates()
    {
        $coins = app(\App\Services\CryptoTradeRateService::class)->configuredCoins();
        $rates = app(\App\Services\CryptoTradeRateService::class)->rates($coins);
        return response()->json($rates);
    }

    /** Return cached provider market prices for the public spot-price panel. */
    public function marketPrices(\App\Services\ExchangeRateService $market): \Illuminate\Http\JsonResponse
    {
        $snapshot = $market->getUsdRates();
        $coins = [];

        $configuredCoins = app(\App\Services\CryptoTradeRateService::class)->configuredCoins();
        foreach (array_intersect_key(\App\Services\ExchangeRateService::SUPPORTED_COINS, array_flip($configuredCoins)) as $symbol => $meta) {
            $price = $snapshot[$symbol] ?? null;
            if (!is_numeric($price) || (float) $price <= 0) continue;

            $coins[] = [
                'symbol' => $symbol,
                'name' => $meta['name'],
                'price_usd' => (float) $price,
                'change_24h' => data_get($snapshot, "_change_24h.{$symbol}"),
                'is_live' => in_array($symbol, $snapshot['_live_coins'] ?? [], true),
            ];
        }

        return response()->json([
            'coins' => $coins,
            'source' => $snapshot['_source'] ?? 'unavailable',
            'updated_at' => $snapshot['_updated_at'] ?? null,
            'stale' => (bool) ($snapshot['_stale'] ?? false),
        ]);
    }
}
