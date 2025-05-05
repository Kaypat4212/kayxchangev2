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
        Schema::table('sell_trades', function (Blueprint $table) {
            $table->string('alt_bank')->nullable()->after('payment_method');
            $table->string('alt_account_number')->nullable()->after('alt_bank');
            $table->string('alt_account_name')->nullable()->after('alt_account_number');
        });
    }
    
    public function down()
    {
        Schema::table('sell_trades', function (Blueprint $table) {
            $table->dropColumn(['alt_bank', 'alt_account_number', 'alt_account_name']);
        });

    }
    
    
};
