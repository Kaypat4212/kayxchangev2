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
        Schema::create('buy_trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('usd_amount', 10, 2);
            $table->decimal('naira_amount', 15, 2);
            $table->string('transaction_type');
            $table->string('wallet_address')->nullable();
            $table->string('payment_proof')->nullable();
            $table->string('coin')->nullable();
            $table->string('network');
            $table->string('status')->default('pending');
            $table->string('sender_name')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('transaction_ref')->unique()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buy_trades');
    }
};
