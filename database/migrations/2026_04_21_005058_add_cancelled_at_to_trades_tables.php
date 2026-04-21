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
            $table->timestamp('cancelled_at')->nullable()->after('approved_at');
            $table->unsignedBigInteger('cancelled_by')->nullable()->after('cancelled_at');
        });

        Schema::table('sell_trades', function (Blueprint $table) {
            $table->timestamp('cancelled_at')->nullable()->after('updated_at');
            $table->unsignedBigInteger('cancelled_by')->nullable()->after('cancelled_at');
        });
    }

    public function down(): void
    {
        Schema::table('buy_trades', function (Blueprint $table) {
            $table->dropColumn(['cancelled_at', 'cancelled_by']);
        });

        Schema::table('sell_trades', function (Blueprint $table) {
            $table->dropColumn(['cancelled_at', 'cancelled_by']);
        });
    }
};
