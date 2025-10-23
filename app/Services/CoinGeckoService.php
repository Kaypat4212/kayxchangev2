<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CoinGeckoService
{
    private $baseUrl;
    private $cachePrefix;

    public function __construct()
    {
        $this->baseUrl = 'https://api.coingecko.com/api/v3';
        $this->cachePrefix = 'coingecko_';
    }

    /**
     * Get cryptocurrency prices from CoinGecko
     */
    public function getCryptoPrices($coinIds = null)
    {
        try {
            // Default popular cryptocurrencies if none specified
            $defaultCoins = [
                'bitcoin', 'ethereum', 'binancecoin', 'cardano', 'solana',
                'ripple', 'polkadot', 'dogecoin', 'avalanche-2', 'polygon',
                'chainlink', 'litecoin', 'bitcoin-cash', 'stellar', 'ethereum-classic',
                'tron', 'monero', 'eos', 'aave', 'maker'
            ];

            $coins = $coinIds ?: $defaultCoins;
            $coinList = is_array($coins) ? implode(',', $coins) : $coins;
            
            // Cache key for this request
            $cacheKey = $this->cachePrefix . 'prices_' . md5($coinList);
            
            // Check cache first (cache for 2 minutes)
            $cachedData = Cache::get($cacheKey);
            if ($cachedData) {
                return $cachedData;
            }

            $response = Http::timeout(10)->get("{$this->baseUrl}/simple/price", [
                'ids' => $coinList,
                'vs_currencies' => 'usd,ngn',
                'include_24hr_change' => 'true',
                'include_market_cap' => 'true',
                'include_24hr_vol' => 'true'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Transform data for easier use
                $transformedData = [];
                foreach ($data as $coinId => $priceData) {
                    $transformedData[] = [
                        'id' => $coinId,
                        'name' => ucfirst(str_replace('-', ' ', $coinId)),
                        'symbol' => $this->getCoinSymbol($coinId),
                        'price_usd' => $priceData['usd'] ?? 0,
                        'price_ngn' => $priceData['ngn'] ?? 0,
                        'change_24h' => $priceData['usd_24h_change'] ?? 0,
                        'market_cap' => $priceData['usd_market_cap'] ?? 0,
                        'volume_24h' => $priceData['usd_24h_vol'] ?? 0,
                    ];
                }

                // Cache the result
                Cache::put($cacheKey, $transformedData, now()->addMinutes(2));
                
                return $transformedData;
            }

            Log::error('CoinGecko API request failed', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return [];

        } catch (\Exception $e) {
            Log::error('Error fetching cryptocurrency prices from CoinGecko', [
                'error' => $e->getMessage()
            ]);

            // Return cached data if available, even if expired
            $cacheKey = $this->cachePrefix . 'prices_' . md5($coinList ?? '');
            return Cache::get($cacheKey, []);
        }
    }

    /**
     * Get specific coin price
     */
    public function getCoinPrice($coinId)
    {
        $prices = $this->getCryptoPrices([$coinId]);
        return $prices[0] ?? null;
    }

    /**
     * Get trending cryptocurrencies
     */
    public function getTrendingCoins()
    {
        try {
            $cacheKey = $this->cachePrefix . 'trending';
            
            $cachedData = Cache::get($cacheKey);
            if ($cachedData) {
                return $cachedData;
            }

            $response = Http::timeout(10)->get("{$this->baseUrl}/search/trending");

            if ($response->successful()) {
                $data = $response->json();
                $trending = [];

                if (isset($data['coins'])) {
                    foreach ($data['coins'] as $coin) {
                        $trending[] = [
                            'id' => $coin['item']['id'],
                            'name' => $coin['item']['name'],
                            'symbol' => strtoupper($coin['item']['symbol']),
                            'rank' => $coin['item']['market_cap_rank'] ?? 0,
                        ];
                    }
                }

                Cache::put($cacheKey, $trending, now()->addMinutes(15));
                return $trending;
            }

            return [];

        } catch (\Exception $e) {
            Log::error('Error fetching trending coins from CoinGecko', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get coin symbol mapping
     */
    private function getCoinSymbol($coinId)
    {
        $symbolMap = [
            'bitcoin' => 'BTC',
            'ethereum' => 'ETH',
            'binancecoin' => 'BNB',
            'cardano' => 'ADA',
            'solana' => 'SOL',
            'ripple' => 'XRP',
            'polkadot' => 'DOT',
            'dogecoin' => 'DOGE',
            'avalanche-2' => 'AVAX',
            'polygon' => 'MATIC',
            'chainlink' => 'LINK',
            'litecoin' => 'LTC',
            'bitcoin-cash' => 'BCH',
            'stellar' => 'XLM',
            'ethereum-classic' => 'ETC',
            'tron' => 'TRX',
            'monero' => 'XMR',
            'eos' => 'EOS',
            'aave' => 'AAVE',
            'maker' => 'MKR'
        ];

        return $symbolMap[$coinId] ?? strtoupper(substr($coinId, 0, 4));
    }

    /**
     * Search for coins
     */
    public function searchCoins($query)
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/search", [
                'query' => $query
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['coins'] ?? [];
            }

            return [];

        } catch (\Exception $e) {
            Log::error('Error searching coins on CoinGecko', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
}