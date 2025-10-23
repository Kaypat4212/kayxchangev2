<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buy_trades', function (Blueprint $table) {
            $table->string('transaction_ref')->unique()->after('ip_address');
        });
    }

    public function down(): void
    {
        Schema::table('buy_trades', function (Blueprint $table) {
            $table->dropColumn('transaction_ref');
        });
    }
};