<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('onboarding_completed')->default(false)->after('kyc_verified');
            $table->string('transaction_pin')->nullable()->after('onboarding_completed');
            $table->tinyInteger('pin_attempts')->default(0)->after('transaction_pin');
            $table->timestamp('pin_locked_until')->nullable()->after('pin_attempts');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['onboarding_completed', 'transaction_pin', 'pin_attempts', 'pin_locked_until']);
        });
    }
};
