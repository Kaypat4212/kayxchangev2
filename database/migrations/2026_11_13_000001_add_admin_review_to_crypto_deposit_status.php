<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE crypto_deposits MODIFY status ENUM('pending','awaiting_admin_review','completed','expired','failed') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::table('crypto_deposits')->where('status', 'awaiting_admin_review')->update(['status' => 'pending']);
            DB::statement("ALTER TABLE crypto_deposits MODIFY status ENUM('pending','completed','expired','failed') NOT NULL DEFAULT 'pending'");
        }
    }
};
