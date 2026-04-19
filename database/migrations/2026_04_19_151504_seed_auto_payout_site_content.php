<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            ['key' => 'auto_payout_enabled',          'group' => 'auto_payout', 'label' => 'Auto Payout Enabled',          'value' => '0'],
            ['key' => 'auto_payout_gateway',           'group' => 'auto_payout', 'label' => 'Auto Payout Gateway',           'value' => 'paystack'],
            ['key' => 'auto_payout_paystack_enabled',  'group' => 'auto_payout', 'label' => 'Auto Payout Paystack Enabled',  'value' => '0'],
            ['key' => 'auto_payout_opay_enabled',      'group' => 'auto_payout', 'label' => 'Auto Payout OPay Enabled',      'value' => '0'],
        ];

        foreach ($rows as $row) {
            DB::table('site_contents')->updateOrInsert(
                ['key' => $row['key']],
                array_merge($row, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }

    public function down(): void
    {
        DB::table('site_contents')->whereIn('key', [
            'auto_payout_enabled',
            'auto_payout_gateway',
            'auto_payout_paystack_enabled',
            'auto_payout_opay_enabled',
        ])->delete();
    }
};
