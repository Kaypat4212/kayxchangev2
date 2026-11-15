<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('crypto_withdrawals', function (Blueprint $table): void {
            $table->uuid('request_key')->nullable()->after('risk_flags')->unique();
            $table->string('provider_payout_id')->nullable()->after('risk_flags');
            $table->string('provider_payout_status', 40)->nullable()->after('provider_payout_id');
            $table->timestamp('provider_payout_started_at')->nullable()->after('provider_payout_status');
            $table->timestamp('provider_payout_verified_at')->nullable()->after('provider_payout_started_at');
            $table->json('provider_payout_response')->nullable()->after('provider_payout_verified_at');
            $table->index(['provider_payout_id']);
        });
    }

    public function down(): void
    {
        Schema::table('crypto_withdrawals', function (Blueprint $table): void {
            $table->dropUnique(['request_key']);
            $table->dropIndex(['provider_payout_id']);
            $table->dropColumn(['request_key', 'provider_payout_id', 'provider_payout_status', 'provider_payout_started_at', 'provider_payout_verified_at', 'provider_payout_response']);
        });
    }
};
