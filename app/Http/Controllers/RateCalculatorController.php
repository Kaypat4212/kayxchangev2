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

    /** CoinGecko-only prices for homepage crypto conversion, limited to configured platform assets. */
    public function converterPrices(): \Illuminate\Http\JsonResponse
    {
        $configuredCoins = app(\App\Services\CryptoTradeRateService::class)->configuredCoins();
        $supported = array_intersect_key(\App\Services\ExchangeRateService::SUPPORTED_COINS, array_flip($configuredCoins));
        if (!$supported) {
            return response()->json(['coins' => [], 'source' => 'coingecko', 'updated_at' => null]);
        }

        $coinIds = array_values(array_unique(array_column($supported, 'coingecko')));
        $cacheKey = 'homepage_coingecko_converter_' . sha1(implode(',', $coinIds));
        $payload = \Illuminate\Support\Facades\Cache::get($cacheKey);
        if (!is_array($payload)) {
            try {
                $apiKey = (string) config('services.coingecko.api_key', '');
                $baseUrl = $apiKey !== '' ? 'https://pro-api.coingecko.com/api/v3' : 'https://api.coingecko.com/api/v3';
                $request = \Illuminate\Support\Facades\Http::acceptJson()->timeout(8);
                if ($apiKey !== '') $request = $request->withHeaders(['x-cg-pro-api-key' => $apiKey]);

                $response = $request->get($baseUrl . '/simple/price', [
                    'ids' => implode(',', $coinIds),
                    'vs_currencies' => 'usd,ngn',
                    'include_last_updated_at' => 'true',
                ]);
                if (!$response->successful()) {
                    return response()->json(['message' => 'CoinGecko prices are temporarily unavailable.'], 503);
                }

                $raw = $response->json();
                $coins = [];
                $updatedAt = null;
                foreach ($supported as $symbol => $meta) {
                    $price = $raw[$meta['coingecko']] ?? [];
                    if (!is_numeric($price['usd'] ?? null) || (float) $price['usd'] <= 0) continue;
                    $coinUpdated = isset($price['last_updated_at']) ? (int) $price['last_updated_at'] : null;
                    if ($coinUpdated && ($updatedAt === null || $coinUpdated < $updatedAt)) $updatedAt = $coinUpdated;
                    $coins[] = [
                        'symbol' => $symbol,
                        'name' => $meta['name'],
                        'price_usd' => (float) $price['usd'],
                        'price_ngn' => is_numeric($price['ngn'] ?? null) ? (float) $price['ngn'] : null,
                        'coingecko_id' => $meta['coingecko'],
                    ];
                }
                if (!$coins) return response()->json(['message' => 'CoinGecko returned no prices for the configured assets.'], 503);

                $payload = [
                    'coins' => $coins,
                    'source' => 'CoinGecko',
                    'updated_at' => $updatedAt ? gmdate(DATE_ATOM, $updatedAt) : now()->toIso8601String(),
                ];
                \Illuminate\Support\Facades\Cache::put($cacheKey, $payload, now()->addSeconds(60));
            } catch (\Throwable $exception) {
                \Illuminate\Support\Facades\Log::warning('Homepage CoinGecko converter request failed.', ['error' => $exception->getMessage()]);
                return response()->json(['message' => 'CoinGecko prices are temporarily unavailable.'], 503);
            }
        }

        return response()->json($payload)->header('Cache-Control', 'public, max-age=30');
    }
}
