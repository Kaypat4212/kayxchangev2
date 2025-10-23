<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CryptoRate;

class CryptoRateSeeder extends Seeder
{
    public function run()
    {
        CryptoRate::upsert([
            ['coin' => 'BTC', 'buy_rate' => 1620.00, 'sell_rate' => 1600.00],
            ['coin' => 'ETH', 'buy_rate' => 1620.00, 'sell_rate' => 1600.00],
            ['coin' => 'USDT', 'buy_rate' => 1625.00, 'sell_rate' => 1600.00],
        ], ['coin'], ['buy_rate', 'sell_rate']);
    }
}