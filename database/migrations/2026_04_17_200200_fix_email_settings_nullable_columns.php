<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_settings', function (Blueprint $table) {
            $table->string('mail_username')->default('')->change();
            $table->string('mail_password')->nullable()->default('')->change();
            $table->string('mail_encryption')->default('tls')->change();
            $table->string('mail_host')->default('')->change();
            $table->string('mail_from_address')->default('')->change();
        });
    }

    public function down(): void
    {
        // intentionally empty — these are safe defaults
    }
};
