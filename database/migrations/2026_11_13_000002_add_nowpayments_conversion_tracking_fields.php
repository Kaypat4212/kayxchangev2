<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversions', function (Blueprint $table) {
            $table->string('provider_conversion_id', 120)->nullable()->after('cryptomus_order_id');
            $table->string('provider_status', 40)->nullable()->after('provider_conversion_id');
            $table->uuid('idempotency_key')->nullable()->unique('conversions_idempotency_key_unique')->after('provider_status');
            $table->index('provider_conversion_id', 'conversions_provider_id_idx');
        });
    }

    public function down(): void
    {
        Schema::table('conversions', function (Blueprint $table) {
            $table->dropIndex('conversions_provider_id_idx');
            $table->dropUnique('conversions_idempotency_key_unique');
            $table->dropColumn(['provider_conversion_id', 'provider_status', 'idempotency_key']);
        });
    }
};
