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
        Schema::table('deposits', function (Blueprint $table) {
            if (!Schema::hasColumn('deposits', 'gateway')) {
                $table->string('gateway')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('deposits', 'gateway_reference')) {
                $table->string('gateway_reference')->nullable()->unique()->after('gateway');
            }
            if (!Schema::hasColumn('deposits', 'gateway_response')) {
                $table->json('gateway_response')->nullable()->after('gateway_reference');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->dropColumn(['gateway', 'gateway_reference', 'gateway_response']);
        });
    }
};
