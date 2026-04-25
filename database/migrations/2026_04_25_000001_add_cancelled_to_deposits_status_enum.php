<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `deposits` MODIFY COLUMN `status` ENUM('pending', 'approved', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Update any 'cancelled' rows back to 'rejected' before reverting the enum
        DB::statement("UPDATE `deposits` SET `status` = 'rejected' WHERE `status` = 'cancelled'");
        DB::statement("ALTER TABLE `deposits` MODIFY COLUMN `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
    }
};
