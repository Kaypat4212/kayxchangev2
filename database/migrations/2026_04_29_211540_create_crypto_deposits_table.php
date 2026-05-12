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
        Schema::create('crypto_deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('wallet_id')->constrained()->onDelete('cascade');
            $table->string('currency');
            $table->decimal('expected_amount', 15, 8);
            $table->decimal('received_amount', 15, 8)->nullable();
            $table->enum('status', ['pending', 'completed', 'expired', 'failed'])->default('pending');
            $table->string('cryptomus_payment_id')->nullable();
            $table->string('payment_url')->nullable();
            $table->string('payment_address')->nullable();
            $table->json('payment_data')->nullable();
            $table->string('transaction_hash')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crypto_deposits');
    }
};
