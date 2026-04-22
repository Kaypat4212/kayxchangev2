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
            $table->string('source', 20)->default('web')->after('status')->comment('web | web_bot | telegram_bot');
        });

        Schema::table('sell_trades', function (Blueprint $table) {
            $table->string('source', 20)->default('web')->after('status')->comment('web | web_bot | telegram_bot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buy_trades', function (Blueprint $table) {
            $table->dropColumn('source');
        });

        Schema::table('sell_trades', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
