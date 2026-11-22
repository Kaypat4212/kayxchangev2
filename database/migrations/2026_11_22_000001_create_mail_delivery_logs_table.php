<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_delivery_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('trace_id')->unique();
            $table->string('message_id', 255)->nullable()->index();
            $table->string('recipient', 320)->nullable()->index();
            $table->string('subject', 255)->nullable();
            $table->string('mailer', 50)->nullable();
            $table->string('status', 24)->default('sending')->index();
            $table->string('retry_type', 32)->nullable();
            $table->foreignId('retry_of_id')->nullable()->constrained('mail_delivery_logs')->nullOnDelete();
            $table->unsignedSmallInteger('attempts')->default(1);
            $table->text('last_error')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_delivery_logs');
    }
};
