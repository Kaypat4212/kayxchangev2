<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversions', function (Blueprint $table) {
            $table->string('mode', 20)->default('cryptomus')->after('status'); // cryptomus | manual
            $table->string('wallet_address', 255)->nullable()->after('mode');
            $table->text('admin_note')->nullable()->after('failure_reason');
            $table->unsignedBigInteger('processed_by')->nullable()->after('admin_note');

            $table->index(['mode', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('conversions', function (Blueprint $table) {
            $table->dropIndex(['mode', 'status']);
            $table->dropColumn(['mode', 'wallet_address', 'admin_note', 'processed_by']);
        });
    }
};
