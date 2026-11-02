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
 * Last resort    : static fallback rates so the convert page never breaks
 */
class ExchangeRateService
{
    /** Coins supported on the convert page, mapped to price-source IDs. */
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

    private const CACHE_KEY = 'convert_usd_rates_v2';
    private const CACHE_TTL = 120; // seconds

    /**
     * Live USD rate per supported coin: ['BTC' => 97500.0, ...]
     * plus metadata under '_source' and '_updated_at' keys.
     */
    public function getUsdRates(): array
    {
        $rates = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return $this->fetchRates();
        });

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
              ?? $this->fetchFromCoinCap()
              ?? $this->staticFallback();

        return $rates;
    }

    private function fetchFromCoinGecko(): ?array
    {
        try {
            $ids = implode(',', array_column(self::SUPPORTED_COINS, 'coingecko'));

            $response = Http::timeout(8)->get('https://api.coingecko.com/api/v3/simple/price', [
                'ids'           => $ids,
                'vs_currencies' => 'usd',
            ]);

            if (!$response->successful()) {
                Log::warning('ExchangeRateService: CoinGecko request failed', ['status' => $response->status()]);
                return null;
            }

            $data  = $response->json();
            $rates = [];

            foreach (self::SUPPORTED_COINS as $symbol => $map) {
                $price = $data[$map['coingecko']]['usd'] ?? null;
                if (is_numeric($price) && $price > 0) {
                    $rates[$symbol] = (float) $price;
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

            return $rates;

        } catch (\Throwable $e) {
            Log::warning('ExchangeRateService: CoinGecko error: ' . $e->getMessage());
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

            foreach (self::SUPPORTED_COINS as $symbol => $map) {
                $price = $data[$map['coincap']]['priceUsd'] ?? null;
                if (is_numeric($price) && $price > 0) {
                    $rates[$symbol] = (float) $price;
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

        return $rates;
    }
}
