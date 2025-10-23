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
            if (!Schema::hasColumn('users', 'telegram_username')) {
                $table->string('telegram_username')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'telegram_notifications')) {
                $table->boolean('telegram_notifications')->default(false)->after('telegram_username');
            }
            if (!Schema::hasColumn('users', 'telegram_chat_id')) {
                $table->string('telegram_chat_id')->nullable()->after('telegram_notifications');
            }
            if (!Schema::hasColumn('users', 'telegram_verified')) {
                $table->boolean('telegram_verified')->default(false)->after('telegram_chat_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'telegram_username',
                'telegram_notifications', 
                'telegram_chat_id',
                'telegram_verified'
            ]);
        });
    }
};
