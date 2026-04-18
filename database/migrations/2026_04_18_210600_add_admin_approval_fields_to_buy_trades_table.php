<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buy_trades', function (Blueprint $table) {
            $table->string('blockchain_txid')->nullable()->after('payment_proof');
            $table->string('admin_payment_proof')->nullable()->after('blockchain_txid');
            $table->foreignId('approved_by_admin_id')->nullable()->after('admin_payment_proof')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by_admin_id');
        });
    }

    public function down(): void
    {
        Schema::table('buy_trades', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by_admin_id');
            $table->dropColumn(['blockchain_txid', 'admin_payment_proof', 'approved_at']);
        });
    }
};
