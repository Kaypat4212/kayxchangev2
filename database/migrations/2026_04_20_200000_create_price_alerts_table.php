<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // 'platform' = our buy/sell rates, 'market' = live CoinGecko price
            $table->enum('type', ['platform', 'market'])->default('platform');
            $table->string('coin', 20);                          // BTC, ETH, USDT
            $table->enum('direction', ['above', 'below']);       // trigger direction
            $table->decimal('target_price', 18, 2);             // price in NGN (platform) or USD (market)
            $table->boolean('notify_telegram')->default(true);
            $table->boolean('notify_email')->default(true);
            $table->boolean('notify_app')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamp('triggered_at')->nullable();       // set when alert fires
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_alerts');
    }
};
