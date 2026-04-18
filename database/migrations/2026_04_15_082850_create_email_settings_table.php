<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_settings', function (Blueprint $table) {
            $table->id();
            // SMTP credentials
            $table->string('mail_mailer')->default('smtp');
            $table->string('mail_host')->default('');
            $table->unsignedSmallInteger('mail_port')->default(587);
            $table->string('mail_username')->default('');
            $table->text('mail_password')->nullable();  // encrypted
            $table->string('mail_encryption')->default('tls');
            $table->string('mail_from_address')->default('');
            $table->string('mail_from_name')->default('KayXchange');
            // Email toggles
            $table->boolean('welcome_email_enabled')->default(true);
            $table->boolean('login_success_email_enabled')->default(true);
            $table->boolean('login_failed_email_enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_settings');
    }
};
