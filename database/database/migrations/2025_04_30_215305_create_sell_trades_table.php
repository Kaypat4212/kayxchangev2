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
        Schema::create('sell_trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('coin');
            $table->decimal('usd_amount', 15, 2);
            $table->decimal('naira_amount', 15, 2);
            $table->string('proof');
            $table->string('payment_method');
            $table->string('status')->default('Pending');
            $table->string('name');
            $table->string('transaction_ref')->unique();
            $table->string('wallet_address');
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sell_trades');
    }
};