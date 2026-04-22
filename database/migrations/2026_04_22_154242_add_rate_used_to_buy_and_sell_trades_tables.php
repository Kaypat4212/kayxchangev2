<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('buy_trades', function (Blueprint $table) {
            $table->decimal('rate_used', 15, 4)->nullable()->after('naira_amount')
                  ->comment('NGN per USD buy rate locked at time of trade submission');
        });

        Schema::table('sell_trades', function (Blueprint $table) {
            $table->decimal('rate_used', 15, 4)->nullable()->after('naira_amount')
                  ->comment('NGN per USD sell rate locked at time of trade submission');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buy_trades', function (Blueprint $table) {
            $table->dropColumn('rate_used');
        });

        Schema::table('sell_trades', function (Blueprint $table) {
            $table->dropColumn('rate_used');
        });
    }
};
