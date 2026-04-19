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
            $table->string('paystack_auth_code')->nullable()->after('balance');
            $table->string('paystack_auth_email')->nullable()->after('paystack_auth_code');
            $table->string('paystack_auth_card_last4')->nullable()->after('paystack_auth_email');
            $table->string('paystack_auth_card_type')->nullable()->after('paystack_auth_card_last4');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'paystack_auth_code',
                'paystack_auth_email',
                'paystack_auth_card_last4',
                'paystack_auth_card_type',
            ]);
        });
    }
};
