<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('portfolio_holdings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('asset', 16);
            $table->decimal('quantity', 28, 12);
            $table->decimal('average_cost_usd', 20, 8)->nullable();
            $table->string('platform', 80)->nullable();
            $table->string('notes', 500)->nullable();
            $table->timestamps();
            $table->index(['user_id', 'asset']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_holdings');
    }
};
