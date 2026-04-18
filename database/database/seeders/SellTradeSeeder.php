<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SellTrade;

class SellTradeSeeder extends Seeder
{
    public function run()
    {
        SellTrade::create([
            'user_id' => 1,
            'name' => 'Test User',
            'coin' => 'ETH',
            'amount' => 200.00, // Assuming amount is in USD
            'usd_amount' => 200.00,
            'naira_amount' => 300000.00, // Add a reasonable NGN value
            'proof' => 'proof3.jpg',
            'status' => 'pending',
            'account_name' => 'John Doe',
            'account_number' => '1234567890',
            'bank_name' => 'Test Bank',
            'payment_method' => 'Bank Transfer',
            'wallet_address' => null, // Optional, can be null
            'payment_proof' => null, // Optional, can be null
        ]);

        SellTrade::create([
            'user_id' => 1,
            'name' => 'Test User',
            'coin' => 'BTC',
            'amount' => 400.00,
            'usd_amount' => 400.00,
            'naira_amount' => 600000.00,
            'proof' => 'proof4.jpg',
            'status' => 'completed',
            'account_name' => 'John Doe',
            'account_number' => '0987654321',
            'bank_name' => 'Test Bank',
            'payment_method' => 'Bank Transfer',
            'wallet_address' => null,
            'payment_proof' => null,
        ]);
    }
}