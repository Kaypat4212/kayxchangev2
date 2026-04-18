<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buy_trades', function (Blueprint $table) {
            $table->string('sender_name')->nullable()->after('naira_amount');
        });
    }

    public function down(): void
    {
        Schema::table('buy_trades', function (Blueprint $table) {
            $table->dropColumn('sender_name');
        });
    }
};