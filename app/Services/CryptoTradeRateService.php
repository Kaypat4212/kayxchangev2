<?php

namespace App\Services;

use App\Models\CryptoRate;

/** Resolves configured NGN-per-USD crypto trade rates with a stablecoin fallback. */
class CryptoTradeRateService
{
    /**
     * Return positive configured buy/sell rates. When an asset has no usable
     * rate, use the corresponding platform USDT rate as its USD/NGN reference.
     */
    public function rates(array $coins): array
    {
        $rows = CryptoRate::query()->get(['coin', 'buy_rate', 'sell_rate'])
            ->keyBy(fn (CryptoRate $rate) => strtoupper((string) $rate->coin));
        $usdt = $rows->get('USDT');
        $fallbackBuy = $this->positive($usdt?->buy_rate);
        $fallbackSell = $this->positive($usdt?->sell_rate);
        $result = [];

        foreach (array_unique(array_map('strtoupper', $coins)) as $coin) {
            $row = $rows->get($coin);
            $result[$coin] = [
                'buy_rate' => $this->positive($row?->buy_rate) ?? $fallbackBuy,
                'sell_rate' => $this->positive($row?->sell_rate) ?? $fallbackSell,
            ];
        }

        return $result;
    }

    public function rate(string $coin, string $side): ?float
    {
        $field = strtolower($side) === 'buy' ? 'buy_rate' : 'sell_rate';
        return $this->rates([strtoupper($coin)])[strtoupper($coin)][$field] ?? null;
    }

    private function positive(mixed $value): ?float
    {
        return is_numeric($value) && (float) $value > 0 ? (float) $value : null;
    }
}
