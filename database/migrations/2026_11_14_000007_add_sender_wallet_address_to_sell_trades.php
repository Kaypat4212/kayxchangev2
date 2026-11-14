<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sell_trades') && !Schema::hasColumn('sell_trades', 'sender_wallet_address')) {
            Schema::table('sell_trades', function (Blueprint $table): void {
                $table->string('sender_wallet_address', 255)->nullable()->after('wallet_address');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sell_trades') && Schema::hasColumn('sell_trades', 'sender_wallet_address')) {
            Schema::table('sell_trades', function (Blueprint $table): void {
                $table->dropColumn('sender_wallet_address');
            });
        }
    }
};
