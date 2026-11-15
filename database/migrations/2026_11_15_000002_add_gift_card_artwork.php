<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('gift_card_rates', function (Blueprint $table): void {
            $table->string('image_path', 255)->nullable()->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('gift_card_rates', function (Blueprint $table): void {
            $table->dropColumn('image_path');
        });
    }
};
