<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->decimal('fee_amount', 12, 2)->default(0)->after('amount');
        });

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->decimal('fee_amount', 12, 2)->default(0)->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->dropColumn('fee_amount');
        });

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropColumn('fee_amount');
        });
    }
};
