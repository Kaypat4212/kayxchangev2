<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            // Add columns if they don't exist
            if (!Schema::hasColumn('withdrawals', 'user_id')) {
                $table->foreignId('user_id')->constrained()->onDelete('cascade')->after('id');
            }
            if (!Schema::hasColumn('withdrawals', 'amount')) {
                $table->decimal('amount', 15, 2)->after('user_id');
            }
            if (!Schema::hasColumn('withdrawals', 'bank_account')) {
                $table->json('bank_account')->nullable()->after('amount');
            }
            if (!Schema::hasColumn('withdrawals', 'status')) {
                $table->string('status')->default('pending')->after('bank_account');
            }
            if (!Schema::hasColumn('withdrawals', 'currency')) {
                $table->string('currency')->default('NGN')->after('status');
            }
            if (!Schema::hasColumn('withdrawals', 'reference')) {
                $table->string('reference')->unique()->after('currency');
            }
            if (!Schema::hasColumn('withdrawals', 'processed_at')) {
                $table->timestamp('processed_at')->nullable()->after('reference');
            }
        });
    }

    public function down(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            if (Schema::hasColumn('withdrawals', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('withdrawals', 'amount')) {
                $table->dropColumn('amount');
            }
            if (Schema::hasColumn('withdrawals', 'bank_account')) {
                $table->dropColumn('bank_account');
            }
            if (Schema::hasColumn('withdrawals', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('withdrawals', 'currency')) {
                $table->dropColumn('currency');
            }
            if (Schema::hasColumn('withdrawals', 'reference')) {
                $table->dropColumn('reference');
            }
            if (Schema::hasColumn('withdrawals', 'processed_at')) {
                $table->dropColumn('processed_at');
            }
        });
    }
};