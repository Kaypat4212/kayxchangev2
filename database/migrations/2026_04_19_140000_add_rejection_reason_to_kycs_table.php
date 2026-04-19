<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kycs', function (Blueprint $table) {
            if (! Schema::hasColumn('kycs', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('status');
            }
            if (! Schema::hasColumn('kycs', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('rejection_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('kycs', function (Blueprint $table) {
            $table->dropColumn(['rejection_reason', 'reviewed_at']);
        });
    }
};
