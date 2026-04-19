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
        Schema::table('users', function (Blueprint $table) {
            $table->string('whatsapp_phone')->nullable()->unique()->after('telegram_chat_id');
            $table->boolean('whatsapp_verified')->default(false)->after('whatsapp_phone');
            $table->boolean('whatsapp_notifications')->default(true)->after('whatsapp_verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['whatsapp_phone', 'whatsapp_verified', 'whatsapp_notifications']);
        });
    }
};
