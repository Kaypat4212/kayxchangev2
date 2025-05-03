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
        Schema::table('buy_trades', function (Blueprint $table) {
            $table->string('status')->default('pending'); // new column
        });
    
        Schema::table('sell_trades', function (Blueprint $table) {
            $table->string('status')->default('pending');
        });
    }
    
 
    

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('buy_trades', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    
        Schema::table('sell_trades', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
