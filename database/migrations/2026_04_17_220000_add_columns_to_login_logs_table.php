<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('login_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('login_logs', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete()->after('id');
            }
            if (!Schema::hasColumn('login_logs', 'email')) {
                $table->string('email')->index()->after('user_id');
            }
            if (!Schema::hasColumn('login_logs', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('email');
            }
            if (!Schema::hasColumn('login_logs', 'user_agent')) {
                $table->text('user_agent')->nullable()->after('ip_address');
            }
            if (!Schema::hasColumn('login_logs', 'status')) {
                $table->enum('status', ['success', 'failed'])->default('failed')->after('user_agent');
            }
        });
    }

    public function down(): void
    {
        Schema::table('login_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['email', 'ip_address', 'user_agent', 'status']);
        });
    }
};
