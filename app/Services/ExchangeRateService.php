<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Multi-source crypto exchange-rate service.
 *
 * Primary source : CoinGecko (free public API, no key required)
 * Fallback source: CoinCap   (free public API, no key required)
 * Last resort    : static fallback rates so the swap quote page remains available
 */
class ExchangeRateService
{
    /** Coins supported on the swap page, mapped to price-source IDs. */
    public const SUPPORTED_COINS = [
        'BTC'  => ['coingecko' => 'bitcoin',      'coincap' => 'bitcoin',     'name' => 'Bitcoin',   'fallback' => 97500.0],
        'ETH'  => ['coingecko' => 'ethereum',     'coincap' => 'ethereum',    'name' => 'Ethereum',  'fallback' => 3600.0],
        'USDT' => ['coingecko' => 'tether',       'coincap' => 'tether',      'name' => 'Tether',    'fallback' => 1.0],
        'USDC' => ['coingecko' => 'usd-coin',     'coincap' => 'usd-coin',    'name' => 'USD Coin',  'fallback' => 1.0],
        'BNB'  => ['coingecko' => 'binancecoin',  'coincap' => 'binance-coin','name' => 'BNB',       'fallback' => 690.0],
        'SOL'  => ['coingecko' => 'solana',       'coincap' => 'solana',      'name' => 'Solana',    'fallback' => 220.0],
        'XRP'  => ['coingecko' => 'ripple',       'coincap' => 'xrp',         'name' => 'XRP',       'fallback' => 2.40],
        'DOGE' => ['coingecko' => 'dogecoin',     'coincap' => 'dogecoin',    'name' => 'Dogecoin',  'fallback' => 0.32],
        'LTC'  => ['coingecko' => 'litecoin',     'coincap' => 'litecoin',    'name' => 'Litecoin',  'fallback' => 105.0],
        'TRX'  => ['coingecko' => 'tron',         'coincap' => 'tron',        'name' => 'TRON',      'fallback' => 0.27],
        'ADA'  => ['coingecko' => 'cardano',      'coincap' => 'cardano',     'name' => 'Cardano',   'fallback' => 1.05],
        'TON'  => ['coingecko' => 'the-open-network', 'coincap' => 'toncoin', 'name' => 'Toncoin',   'fallback' => 5.40],
    ];

    private const CACHE_KEY = 'market_usd_rates_v3';
    private const STALE_CACHE_KEY = 'market_usd_rates_stale_v3';
    private const CACHE_TTL = 60; // seconds

    /**
     * Live USD rate per supported coin: ['BTC' => 97500.0, ...]
     * plus metadata under '_source' and '_updated_at' keys.
     */
    public function getUsdRates(): array
    {
        $cached = Cache::get(self::CACHE_KEY);
        if (is_array($cached)) return $cached;

        $rates = $this->fetchRates();
        if (($rates['_source'] ?? '') !== 'fallback') {
            Cache::put(self::CACHE_KEY, $rates, now()->addSeconds(self::CACHE_TTL));
            Cache::put(self::STALE_CACHE_KEY, $rates, now()->addDay());
            return $rates;
        }

        $lastKnown = Cache::get(self::STALE_CACHE_KEY);
        if (is_array($lastKnown)) {
            $lastKnown['_stale'] = true;
            return $lastKnown;
        }

        return $rates;
    }

    /**
     * Build a conversion quote.
     *
     * @return array{rate:float, to_amount:float, fee_amount:float, fee_percent:float, receive_amount:float, usd_value:float, source:string}
     */
    public function quote(string $fromCoin, string $toCoin, float $fromAmount, float $feePercent): array
    {
        $rates = $this->getUsdRates();

        $fromRate = $rates[$fromCoin] ?? null;
        $toRate   = $rates[$toCoin] ?? null;

        if (!$fromRate || !$toRate || $fromRate <= 0 || $toRate <= 0) {
            throw new \InvalidArgumentException("Rate unavailable for {$fromCoin}/{$toCoin}");
        }

        $usdValue      = $fromAmount * $fromRate;
        $pairRate      = $fromRate / $toRate;           // 1 FROM = X TO
        $grossToAmount = $fromAmount * $pairRate;
        $feeAmount     = $grossToAmount * ($feePercent / 100);
        $receiveAmount = max($grossToAmount - $feeAmount, 0);

        return [
            'rate'           => $pairRate,
            'to_amount'      => $grossToAmount,
            'fee_amount'     => $feeAmount,
            'fee_percent'    => $feePercent,
            'receive_amount' => $receiveAmount,
            'usd_value'      => $usdValue,
            'source'         => $rates['_source'] ?? 'unknown',
        ];
    }

    public function isSupported(string $coin): bool
    {
        return isset(self::SUPPORTED_COINS[strtoupper($coin)]);
    }

    // ─── Internals ──────────────────────────────────────────────────────────

    private function fetchRates(): array
    {
        $rates = $this->fetchFromCoinGecko()
              ?? $this->fetchFromCoinMarketCap()
              ?? $this->fetchFromCoinCap()
              ?? $this->staticFallback();

        return $rates;
    }

    private function fetchFromCoinGecko(): ?array
    {
        try {
            $ids = implode(',', array_column(self::SUPPORTED_COINS, 'coingecko'));

            $apiKey = config('services.coingecko.api_key');
            $baseUrl = $apiKey ? 'https://pro-api.coingecko.com/api/v3' : 'https://api.coingecko.com/api/v3';
            $request = Http::timeout(7)->acceptJson();
            if ($apiKey) $request = $request->withHeaders(['x-cg-pro-api-key' => $apiKey]);
            $response = $request->get($baseUrl . '/simple/price', [
                'ids'           => $ids,
                'vs_currencies' => 'usd',
                'include_24hr_change' => 'true',
                'include_last_updated_at' => 'true',
            ]);

            if (!$response->successful()) {
                Log::warning('ExchangeRateService: CoinGecko request failed', ['status' => $response->status()]);
                return null;
            }

            $data  = $response->json();
            $rates = [];
            $changes = [];
            $liveCoins = [];

            foreach (self::SUPPORTED_COINS as $symbol => $map) {
                $price = $data[$map['coingecko']]['usd'] ?? null;
                if (is_numeric($price) && $price > 0) {
                    $rates[$symbol] = (float) $price;
                    $liveCoins[] = $symbol;
                    $changes[$symbol] = isset($data[$map['coingecko']]['usd_24h_change'])
                        ? (float) $data[$map['coingecko']]['usd_24h_change'] : null;
                }
            }

            if (count($rates) < 3) {
                return null; // suspiciously incomplete — try next source
            }

            // Fill any gaps from static fallback so every coin is quotable
            foreach (self::SUPPORTED_COINS as $symbol => $map) {
                $rates[$symbol] = $rates[$symbol] ?? $map['fallback'];
            }

            $rates['_source']     = 'coingecko';
            $rates['_updated_at'] = now()->toIso8601String();
            $rates['_live_coins'] = $liveCoins;
            $rates['_change_24h'] = $changes;

            return $rates;

        } catch (\Throwable $e) {
            Log::warning('ExchangeRateService: CoinGecko error: ' . $e->getMessage());
            return null;
        }
    }

    private function fetchFromCoinMarketCap(): ?array
    {
        try {
            $slugs = [
                'BTC'=>'bitcoin', 'ETH'=>'ethereum', 'USDT'=>'tether', 'USDC'=>'usd-coin',
                'BNB'=>'bnb', 'SOL'=>'solana', 'XRP'=>'xrp', 'DOGE'=>'dogecoin',
                'LTC'=>'litecoin', 'TRX'=>'tron', 'ADA'=>'cardano', 'TON'=>'toncoin',
            ];
            $apiKey = (string) config('services.coinmarketcap.api_key', '');
            $baseUrl = $apiKey
                ? 'https://pro-api.coinmarketcap.com/v3/cryptocurrency/quotes/latest'
                : 'https://pro-api.coinmarketcap.com/public-api/v3/cryptocurrency/quotes/latest';
            $request = Http::timeout(7)->acceptJson();
            if ($apiKey) $request = $request->withHeaders(['X-CMC_PRO_API_KEY' => $apiKey]);
            $response = $request->get($baseUrl, [
                'slug' => implode(',', array_values($slugs)),
                'convert' => 'USD',
                'skip_invalid' => 'true',
            ]);
            if (!$response->successful()) {
                Log::warning('ExchangeRateService: CoinMarketCap request failed', ['status' => $response->status()]);
                return null;
            }

            $rows = $response->json('data', []);
            if (!is_array($rows)) return null;
            $rates = [];
            $changes = [];
            $liveCoins = [];
            foreach ($rows as $row) {
                if (!is_array($row)) continue;
                $symbol = strtoupper((string) ($row['symbol'] ?? ''));
                if (!isset(self::SUPPORTED_COINS[$symbol])) continue;
                $quote = $row['quote'] ?? [];
                if (array_is_list($quote)) {
                    $quote = collect($quote)->first(fn ($item) => strtoupper((string)($item['symbol'] ?? '')) === 'USD') ?? ($quote[0] ?? []);
                } else {
                    $quote = $quote['USD'] ?? [];
                }
                $price = $quote['price'] ?? null;
                if (!is_numeric($price) || (float) $price <= 0) continue;
                $rates[$symbol] = (float) $price;
                $changes[$symbol] = isset($quote['percent_change_24h']) ? (float) $quote['percent_change_24h'] : null;
                $liveCoins[] = $symbol;
            }

            if (count($rates) < 3) return null;
            foreach (self::SUPPORTED_COINS as $symbol => $map) $rates[$symbol] = $rates[$symbol] ?? $map['fallback'];
            $rates['_source'] = 'coinmarketcap';
            $rates['_updated_at'] = now()->toIso8601String();
            $rates['_live_coins'] = $liveCoins;
            $rates['_change_24h'] = $changes;
            return $rates;
        } catch (\Throwable $e) {
            Log::warning('ExchangeRateService: CoinMarketCap error: ' . $e->getMessage());
            return null;
        }
    }

    private function fetchFromCoinCap(): ?array
    {
        try {
            $ids = implode(',', array_column(self::SUPPORTED_COINS, 'coincap'));

            $response = Http::timeout(8)->get('https://api.coincap.io/v2/assets', [
                'ids' => $ids,
            ]);

            if (!$response->successful()) {
                Log::warning('ExchangeRateService: CoinCap request failed', ['status' => $response->status()]);
                return null;
            }

            $data  = collect($response->json('data') ?? [])->keyBy('id');
            $rates = [];
            $liveCoins = [];
            $changes = [];

            foreach (self::SUPPORTED_COINS as $symbol => $map) {
                $price = $data[$map['coincap']]['priceUsd'] ?? null;
                if (is_numeric($price) && $price > 0) {
                    $rates[$symbol] = (float) $price;
                    $liveCoins[] = $symbol;
                    $changes[$symbol] = isset($data[$map['coincap']]['changePercent24Hr'])
                        ? (float) $data[$map['coincap']]['changePercent24Hr'] : null;
                }
            }

            if (count($rates) < 3) {
                return null;
            }

            foreach (self::SUPPORTED_COINS as $symbol => $map) {
                $rates[$symbol] = $rates[$symbol] ?? $map['fallback'];
            }

            $rates['_source']     = 'coincap';
            $rates['_updated_at'] = now()->toIso8601String();
            $rates['_live_coins'] = $liveCoins;
            $rates['_change_24h'] = $changes;

            return $rates;

        } catch (\Throwable $e) {
            Log::warning('ExchangeRateService: CoinCap error: ' . $e->getMessage());
            return null;
        }
    }

    private function staticFallback(): array
    {
        $rates = [];
        foreach (self::SUPPORTED_COINS as $symbol => $map) {
            $rates[$symbol] = $map['fallback'];
        }
        $rates['_source']     = 'fallback';
        $rates['_updated_at'] = now()->toIso8601String();
        $rates['_live_coins'] = [];
        $rates['_change_24h'] = [];
        $rates['_stale'] = true;

        return $rates;
    }
}
