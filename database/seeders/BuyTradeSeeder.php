<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BuyTrade;

class BuyTradeSeeder extends Seeder
{
    public function run()
    {
        BuyTrade::create([
            'user_id' => 1, // Match your logged-in user's ID
            'coin' => 'BTC',
            'usd_amount' => 500.00,
            'naira_amount' => 750000.00,
            'network' => 'BTC',
            'wallet_address' => '1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa',
            'payment_proof' => 'proof1.jpg',
            'status' => 'completed',
        ]);

        BuyTrade::create([
            'user_id' => 1,
            'coin' => 'ETH',
            'usd_amount' => 300.00,
            'naira_amount' => 450000.00,
            'network' => 'ETH',
            'wallet_address' => '0x1234567890abcdef1234567890abcdef12345678',
            'payment_proof' => 'proof2.jpg',
            'status' => 'pending',
        ]);
    }
}