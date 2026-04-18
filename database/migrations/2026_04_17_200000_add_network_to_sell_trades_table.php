<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sell_trades', function (Blueprint $table) {
            // Network the user is sending on (e.g. ERC20, TRC20, BEP20, SOL, BTC)
            $table->string('network', 20)->nullable()->after('coin');
        });
    }

    public function down(): void
    {
        Schema::table('sell_trades', function (Blueprint $table) {
            $table->dropColumn('network');
        });
    }
};
