<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_bot_messages', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('chat_id');
            $table->string('username')->nullable();
            $table->string('first_name')->nullable();
            $table->integer('user_id')->nullable()->index(); // FK to users if linked
            $table->text('message_text')->nullable();
            $table->string('message_type')->default('text'); // text, photo, command, callback
            $table->string('state_at_time')->nullable();     // what state the user was in
            $table->boolean('is_command')->default(false);
            $table->timestamps();

            $table->index('chat_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_bot_messages');
    }
};
