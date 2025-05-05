<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rates', function (Blueprint $table) {
            $table->decimal('buy_rate', 10, 2)->nullable();  // Add buy_rate column
            $table->decimal('sell_rate', 10, 2)->nullable();  // Add sell_rate column
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rates', function (Blueprint $table) {
            $table->dropColumn(['buy_rate', 'sell_rate']);
        });
    }
};
