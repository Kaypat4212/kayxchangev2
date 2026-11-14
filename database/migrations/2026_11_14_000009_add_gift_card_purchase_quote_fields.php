<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('gift_card_rates')) {
            Schema::table('gift_card_rates', function (Blueprint $table): void {
                if (!Schema::hasColumn('gift_card_rates', 'purchase_markup_ngn')) {
                    $table->decimal('purchase_markup_ngn', 14, 2)->default(3000)->after('sell_rate');
                }
                if (!Schema::hasColumn('gift_card_rates', 'usd_exchange_rate')) {
                    $table->decimal('usd_exchange_rate', 14, 8)->nullable()->after('purchase_markup_ngn');
                }
            });

            DB::table('gift_card_rates')->whereNull('purchase_markup_ngn')->update(['purchase_markup_ngn' => 3000]);
            DB::table('gift_card_rates')->where('currency', 'USD')->update(['usd_exchange_rate' => 1]);
        }

        if (Schema::hasTable('gift_card_orders')) {
            Schema::table('gift_card_orders', function (Blueprint $table): void {
                if (!Schema::hasColumn('gift_card_orders', 'usd_exchange_rate')) {
                    $table->decimal('usd_exchange_rate', 14, 8)->nullable()->after('unit_rate_ngn');
                }
                if (!Schema::hasColumn('gift_card_orders', 'base_total_ngn')) {
                    $table->decimal('base_total_ngn', 14, 2)->nullable()->after('usd_exchange_rate');
                }
                if (!Schema::hasColumn('gift_card_orders', 'purchase_markup_ngn')) {
                    $table->decimal('purchase_markup_ngn', 14, 2)->default(3000)->after('base_total_ngn');
                }
                if (!Schema::hasColumn('gift_card_orders', 'usd_face_value')) {
                    $table->decimal('usd_face_value', 14, 2)->nullable()->after('purchase_markup_ngn');
                }
                if (!Schema::hasColumn('gift_card_orders', 'effective_ngn_per_usd')) {
                    $table->decimal('effective_ngn_per_usd', 14, 2)->nullable()->after('usd_face_value');
                }
            });

            DB::table('gift_card_orders')->update([
                'base_total_ngn' => DB::raw('total_ngn'),
                'purchase_markup_ngn' => 0,
            ]);
            DB::table('gift_card_orders')->where('currency', 'USD')->update([
                'usd_exchange_rate' => 1,
                'usd_face_value' => DB::raw('face_value'),
                'effective_ngn_per_usd' => DB::raw('unit_rate_ngn'),
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('gift_card_orders')) {
            Schema::table('gift_card_orders', function (Blueprint $table): void {
                foreach (['effective_ngn_per_usd', 'usd_face_value', 'purchase_markup_ngn', 'base_total_ngn', 'usd_exchange_rate'] as $column) {
                    if (Schema::hasColumn('gift_card_orders', $column)) $table->dropColumn($column);
                }
            });
        }

        if (Schema::hasTable('gift_card_rates')) {
            Schema::table('gift_card_rates', function (Blueprint $table): void {
                foreach (['usd_exchange_rate', 'purchase_markup_ngn'] as $column) {
                    if (Schema::hasColumn('gift_card_rates', $column)) $table->dropColumn($column);
                }
            });
        }
    }
};
