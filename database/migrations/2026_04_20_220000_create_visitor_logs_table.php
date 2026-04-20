<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitor_logs', function (Blueprint $table) {
            $table->id();
            $table->string('ip', 45)->index();
            $table->string('method', 10)->default('GET');
            $table->string('url', 500);
            $table->string('route_name', 120)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('browser', 80)->nullable();
            $table->string('platform', 60)->nullable();
            $table->boolean('is_mobile')->default(false);
            $table->string('country', 80)->nullable();
            $table->string('country_code', 4)->nullable();
            $table->string('region', 80)->nullable();
            $table->string('city', 80)->nullable();
            $table->string('isp', 150)->nullable();
            $table->string('referer', 500)->nullable();
            $table->boolean('is_bot')->default(false);
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->boolean('telegram_notified')->default(false);
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_logs');
    }
};
