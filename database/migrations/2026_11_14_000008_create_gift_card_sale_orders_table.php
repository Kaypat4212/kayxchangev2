<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('gift_card_sale_orders')) return;
        Schema::create('gift_card_sale_orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('gift_card_rate_id')->constrained('gift_card_rates')->restrictOnDelete();
            $table->string('reference', 64)->unique();
            $table->string('card_name', 100);
            $table->string('country', 4);
            $table->string('currency', 8);
            $table->decimal('face_value', 14, 2);
            $table->decimal('unit_rate_ngn', 14, 2);
            $table->decimal('estimated_ngn', 14, 2);
            $table->decimal('approved_ngn', 14, 2)->nullable();
            $table->text('card_code');
            $table->string('image_path', 255)->nullable();
            $table->text('customer_note')->nullable();
            $table->text('admin_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 24)->default('pending');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void { Schema::dropIfExists('gift_card_sale_orders'); }
};
