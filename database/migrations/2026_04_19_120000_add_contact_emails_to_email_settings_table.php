<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('email_settings', 'support_email')) {
                $table->string('support_email', 255)->nullable()->after('mail_from_name');
            }
            if (! Schema::hasColumn('email_settings', 'security_email')) {
                $table->string('security_email', 255)->nullable()->after('support_email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('email_settings', function (Blueprint $table) {
            $table->dropColumn(['support_email', 'security_email']);
        });
    }
};
