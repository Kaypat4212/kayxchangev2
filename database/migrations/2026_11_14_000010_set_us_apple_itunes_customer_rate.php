<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('gift_card_rates')
            || !Schema::hasColumn('gift_card_rates', 'purchase_markup_ngn')
            || !Schema::hasColumn('gift_card_rates', 'usd_exchange_rate')) {
            return;
        }

        DB::table('gift_card_rates')
            ->where('name', 'iTunes / Apple')
            ->where('country', 'US')
            ->where('currency', 'USD')
            ->update([
                'sell_rate' => 1590,
                'purchase_markup_ngn' => 3000,
                'usd_exchange_rate' => 1,
            ]);
    }

    public function down(): void
    {
        // Keep the configured customer rate and service fee on rollback.
    }
};
