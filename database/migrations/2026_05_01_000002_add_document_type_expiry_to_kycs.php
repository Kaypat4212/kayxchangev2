<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kycs', function (Blueprint $table) {
            $table->string('document_type')->nullable()->after('id_document_path');
            $table->date('expiry_date')->nullable()->after('document_type');
        });
    }

    public function down(): void
    {
        Schema::table('kycs', function (Blueprint $table) {
            $table->dropColumn(['document_type', 'expiry_date']);
        });
    }
};
