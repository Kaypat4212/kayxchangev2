<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->decimal('amount', 15, 2)->nullable()->change();
            $table->string('status')->nullable()->default('pending')->change();
            $table->string('currency')->nullable()->default('NGN')->change();
            $table->string('reference')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->decimal('amount', 15, 2)->nullable(false)->change();
            $table->string('status')->nullable(false)->default('pending')->change();
            $table->string('currency')->nullable(false)->default('NGN')->change();
            $table->string('reference')->nullable(false)->change();
        });
    }
};
