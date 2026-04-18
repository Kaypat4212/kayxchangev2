<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            ['key' => 'pm_enabled_bank_transfer', 'group' => 'payment_methods', 'label' => 'Bank Transfer',  'value' => '1'],
            ['key' => 'pm_enabled_paystack',       'group' => 'payment_methods', 'label' => 'Paystack',       'value' => '1'],
            ['key' => 'pm_enabled_korapay',        'group' => 'payment_methods', 'label' => 'Korapay',        'value' => '1'],
            ['key' => 'pm_enabled_flutterwave',    'group' => 'payment_methods', 'label' => 'Flutterwave',    'value' => '1'],
        ];

        foreach ($rows as $row) {
            DB::table('site_contents')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        DB::table('site_contents')->where('group', 'payment_methods')->delete();
    }
};
