<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blacklists', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['ip', 'user'])->index();
            $table->string('value')->index(); // IP address or user_id
            $table->string('reason')->nullable();
            $table->unsignedBigInteger('blocked_by')->nullable(); // admin user id
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['type', 'value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blacklists');
    }
};
