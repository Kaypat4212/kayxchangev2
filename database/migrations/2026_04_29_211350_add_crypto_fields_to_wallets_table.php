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
        Schema::table('wallets', function (Blueprint $table) {
            $table->string('address')->nullable()->after('currency');
            $table->string('network')->nullable()->after('address');
            $table->boolean('is_active')->default(true)->after('network');
            $table->index(['user_id', 'currency']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'currency']);
            $table->dropColumn(['address', 'network', 'is_active']);
        });
    }
};
