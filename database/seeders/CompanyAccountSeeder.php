<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CompanyAccount;

class CompanyAccountSeeder extends Seeder
{
    public function run()
    {
        CompanyAccount::create([
            'bank_name' => 'First Bank',
            'account_number' => '1234567890',
            'account_name' => 'Kayxchange Ltd',
        ]);
    }
}