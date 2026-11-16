<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'payout_details_changed_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('payout_details_changed_at')->nullable()->after('account_number');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'payout_details_changed_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('payout_details_changed_at');
            });
        }
    }
};
