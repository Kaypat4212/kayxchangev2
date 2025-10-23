<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCompanyAccountsTable extends Migration
{
    public function up()
    {
        Schema::table('company_accounts', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('company_accounts', 'bank_name')) {
                $table->string('bank_name')->after('id');
            }
            if (!Schema::hasColumn('company_accounts', 'account_number')) {
                $table->string('account_number', 10)->after('bank_name');
            }
            if (!Schema::hasColumn('company_accounts', 'account_name')) {
                $table->string('account_name')->after('account_number');
            }
            if (!Schema::hasColumn('company_accounts', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    public function down()
    {
        Schema::table('company_accounts', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'account_number', 'account_name', 'created_at', 'updated_at']);
        });
    }
}