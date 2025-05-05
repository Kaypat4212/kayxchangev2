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
            $table->decimal('amount', 16, 2)->after('coin');
        });
    }
    
    public function down()
    {
        Schema::table('sell_trades', function (Blueprint $table) {
            $table->dropColumn('amount');
        });
    }
};    