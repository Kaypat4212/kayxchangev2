<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crypto_deposits', function (Blueprint $table) {
            $table->decimal('ngn_credited_amount', 15, 2)->nullable()->after('received_amount');
            $table->decimal('ngn_rate_used', 15, 8)->nullable()->after('ngn_credited_amount');
        });
    }

    public function down(): void
    {
        Schema::table('crypto_deposits', function (Blueprint $table) {
            $table->dropColumn(['ngn_credited_amount', 'ngn_rate_used']);
        });
    }
};
