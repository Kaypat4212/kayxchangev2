<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sell_trades', function (Blueprint $table) {
            $table->string('admin_payment_proof')->nullable()->after('payment_proof');
        });
    }

    public function down(): void
    {
        Schema::table('sell_trades', function (Blueprint $table) {
            $table->dropColumn('admin_payment_proof');
        });
    }
};
