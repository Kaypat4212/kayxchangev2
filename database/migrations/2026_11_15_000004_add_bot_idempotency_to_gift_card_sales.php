<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('gift_card_sale_orders') && !Schema::hasColumn('gift_card_sale_orders', 'bot_submission_token')) {
            Schema::table('gift_card_sale_orders', function (Blueprint $table): void {
                $table->string('bot_submission_token', 64)->nullable()->unique();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('gift_card_sale_orders') && Schema::hasColumn('gift_card_sale_orders', 'bot_submission_token')) {
            Schema::table('gift_card_sale_orders', function (Blueprint $table): void {
                $table->dropUnique(['bot_submission_token']);
                $table->dropColumn('bot_submission_token');
            });
        }
    }
};
