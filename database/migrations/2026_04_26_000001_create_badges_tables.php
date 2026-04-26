<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('emoji', 10)->default('🏅');
            $table->text('description')->nullable();
            $table->string('category'); // trader_tier | volume_tier | account | special
            $table->string('criteria_type'); // trade_count | trade_volume_ngn | kyc_verified | bank_added | pin_set | genesis | admin_awarded
            $table->unsignedBigInteger('criteria_value')->default(0); // threshold number
            $table->string('color', 20)->default('#00cc00'); // hex for badge ring colour
            $table->string('rarity', 20)->default('common'); // common | rare | legendary
            $table->boolean('is_special')->default(false); // admin-only award
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('badge_id')->constrained()->onDelete('cascade');
            $table->foreignId('awarded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('awarded_at');
            $table->boolean('is_pinned')->default(false);
            $table->unsignedTinyInteger('pin_position')->nullable(); // 1, 2, or 3
            $table->timestamps();

            $table->unique(['user_id', 'badge_id']);
            $table->index(['user_id', 'is_pinned']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('badges');
    }
};
