<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sell_trades') && !Schema::hasColumn('sell_trades', 'blockchain_txid')) {
            Schema::table('sell_trades', function (Blueprint $table) {
                $table->string('blockchain_txid', 255)->nullable()->unique();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sell_trades') && Schema::hasColumn('sell_trades', 'blockchain_txid')) {
            Schema::table('sell_trades', function (Blueprint $table) {
                $table->dropUnique(['blockchain_txid']);
                $table->dropColumn('blockchain_txid');
            });
        }
    }
};
