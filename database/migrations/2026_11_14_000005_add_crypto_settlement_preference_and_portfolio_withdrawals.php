<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (!Schema::hasColumn('users', 'crypto_deposit_settlement_preference')) {
                $table->string('crypto_deposit_settlement_preference', 8)->default('usd');
            }
        });

        Schema::create('portfolio_withdrawals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reference', 100)->unique();
            $table->json('asset_amounts');
            $table->decimal('usd_value', 24, 8);
            $table->decimal('ngn_rate', 18, 4);
            $table->decimal('naira_amount', 24, 2);
            $table->string('status', 24)->default('completed');
            $table->json('rate_snapshot')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_withdrawals');
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'crypto_deposit_settlement_preference')) {
                $table->dropColumn('crypto_deposit_settlement_preference');
            }
        });
    }
};
