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
        Schema::table('buy_trades', function (Blueprint $table) {
            $table->string('coin')->after('user_id'); // or after any column you prefer
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buy_trades', function (Blueprint $table) {
            $table->dropColumn('coin');
        });
    }
};
