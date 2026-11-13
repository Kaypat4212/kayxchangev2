<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bill_provider_settings', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->string('active_providers')->default('');
            $table->boolean('vtpass_sandbox')->default(true);
            $table->text('vtpass_api_key')->nullable();
            $table->text('vtpass_public_key')->nullable();
            $table->text('vtpass_secret_key')->nullable();
            $table->boolean('monnify_sandbox')->default(true);
            $table->text('monnify_api_key')->nullable();
            $table->text('monnify_secret_key')->nullable();
            $table->string('monnify_contract_code')->nullable();
            $table->timestamps();
        });

        Schema::create('bill_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reference', 64)->unique();
            $table->string('provider', 20);
            $table->string('category', 40);
            $table->string('biller_code', 100)->nullable();
            $table->string('service_code', 100);
            $table->string('product_code', 150)->nullable();
            $table->text('customer_identifier');
            $table->string('customer_name')->nullable();
            $table->string('phone', 30)->nullable();
            $table->decimal('amount', 15, 2);
            $table->decimal('fee', 15, 2)->default(0);
            $table->string('status', 24)->default('validated');
            $table->string('provider_reference', 150)->nullable()->index();
            $table->timestamp('refunded_at')->nullable();
            $table->longText('validation_data')->nullable();
            $table->longText('provider_response')->nullable();
            $table->string('receipt_token')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_payments');
        Schema::dropIfExists('bill_provider_settings');
    }
};
