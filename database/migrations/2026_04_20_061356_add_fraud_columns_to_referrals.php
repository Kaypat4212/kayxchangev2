<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('referrals', function (Blueprint $table) {
            $table->boolean('fraud_flagged')->default(false)->after('status');
            $table->string('fraud_reason')->nullable()->after('fraud_flagged');
            $table->timestamp('blocked_at')->nullable()->after('fraud_reason');
        });
    }

    public function down(): void
    {
        Schema::table('referrals', function (Blueprint $table) {
            $table->dropColumn(['fraud_flagged', 'fraud_reason', 'blocked_at']);
        });
    }
};
