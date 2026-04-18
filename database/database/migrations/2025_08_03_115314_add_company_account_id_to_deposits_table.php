<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->unsignedBigInteger('company_account_id')->nullable();

            // If it's a foreign key to another table (optional):
            // $table->foreign('company_account_id')->references('id')->on('company_accounts')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('deposits', function (Blueprint $table) {
            // If foreign key was used, drop it first
            // $table->dropForeign(['company_account_id']);
            $table->dropColumn('company_account_id');
        });
    }
};
