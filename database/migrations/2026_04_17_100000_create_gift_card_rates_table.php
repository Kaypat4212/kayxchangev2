<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gift_card_rates', function (Blueprint $table) {
            $table->id();
            $table->string('name');          // e.g. Amazon, iTunes
            $table->string('country');       // e.g. US, UK, CA
            $table->string('currency', 10);  // e.g. USD, GBP, CAD
            $table->string('category', 50);  // e.g. retail, gaming, streaming
            $table->decimal('buy_rate', 10, 2)->default(0);  // NGN per unit
            $table->decimal('sell_rate', 10, 2)->default(0); // NGN per unit
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_card_rates');
    }
};
