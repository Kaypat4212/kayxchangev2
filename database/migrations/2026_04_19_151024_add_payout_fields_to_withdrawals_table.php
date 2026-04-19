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
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->string('payout_gateway')->nullable()->after('status');    // 'paystack' | 'opay' | null
            $table->string('payout_reference')->nullable()->after('payout_gateway'); // gateway transfer ref
            $table->string('payout_status')->nullable()->after('payout_reference');  // 'pending'|'success'|'failed'
            $table->string('payout_recipient_code')->nullable()->after('payout_status'); // Paystack recipient code
            $table->text('payout_response')->nullable()->after('payout_recipient_code'); // raw gateway JSON
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropColumn(['payout_gateway', 'payout_reference', 'payout_status', 'payout_recipient_code', 'payout_response']);
        });
    }
};
