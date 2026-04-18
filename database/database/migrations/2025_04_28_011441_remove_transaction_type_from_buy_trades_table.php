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
            $table->dropColumn('transaction_type');
        });
    }
    
    public function down()
    {
        Schema::table('buy_trades', function (Blueprint $table) {
            $table->string('transaction_type')->nullable(); // or set a default if needed
        });
    }
    
};
