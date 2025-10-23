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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('type'); // 'buy' or 'sell'
            $table->unsignedBigInteger('trade_id'); // id from buy_trades or sell_trades
            $table->string('coin');
            $table->decimal('amount_usd', 20, 2);
            $table->decimal('amount_ngn', 20, 2);
            $table->string('status'); // e.g., pending, completed, failed
            $table->timestamps();
        
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
