<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('referrals', function (Blueprint $table) {
            // 0–100 risk score; >= 70 = auto-block, 30-69 = flagged for review, <30 = clean
            $table->unsignedTinyInteger('risk_score')->default(0)->after('blocked_at');
            $table->json('risk_signals')->nullable()->after('risk_score'); // array of contributing signals
        });
    }

    public function down(): void
    {
        Schema::table('referrals', function (Blueprint $table) {
            $table->dropColumn(['risk_score', 'risk_signals']);
        });
    }
};
