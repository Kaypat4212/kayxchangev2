<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('deposits', 'admin_note')) {
            Schema::table('deposits', function (Blueprint $table) {
                $table->string('admin_note', 255)->nullable()->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('deposits', 'admin_note')) {
            Schema::table('deposits', function (Blueprint $table) {
                $table->dropColumn('admin_note');
            });
        }
    }
};
