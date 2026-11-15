<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('gift_card_orders')) return;
        Schema::table('gift_card_orders', function (Blueprint $table): void {
            if (!Schema::hasColumn('gift_card_orders', 'delivery_email_sent_at')) $table->timestamp('delivery_email_sent_at')->nullable();
            if (!Schema::hasColumn('gift_card_orders', 'buyer_email_sent_at')) $table->timestamp('buyer_email_sent_at')->nullable();
            if (!Schema::hasColumn('gift_card_orders', 'delivery_telegram_sent_at')) $table->timestamp('delivery_telegram_sent_at')->nullable();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('gift_card_orders')) return;
        Schema::table('gift_card_orders', function (Blueprint $table): void {
            foreach (['delivery_email_sent_at', 'buyer_email_sent_at', 'delivery_telegram_sent_at'] as $column) {
                if (Schema::hasColumn('gift_card_orders', $column)) $table->dropColumn($column);
            }
        });
    }
};
