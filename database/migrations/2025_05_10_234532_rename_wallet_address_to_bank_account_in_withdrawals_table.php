<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Check if the column exists to avoid errors
        if (Schema::hasColumn('withdrawals', 'wallet_address')) {
            Schema::table('withdrawals', function (Blueprint $table) {
                // Ensure the column definition is set before changing
                $table->json('wallet_address')->nullable()->change();
                // Use CHANGE COLUMN for MySQL/MariaDB compatibility
                DB::statement('ALTER TABLE `withdrawals` CHANGE COLUMN `wallet_address` `bank_account` JSON NULL');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('withdrawals', 'bank_account')) {
            Schema::table('withdrawals', function (Blueprint $table) {
                $table->json('bank_account')->nullable()->change();
                DB::statement('ALTER TABLE `withdrawals` CHANGE COLUMN `bank_account` `wallet_address` JSON NULL');
            });
        }
    }
};