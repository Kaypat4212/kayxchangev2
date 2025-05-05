<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNetworkToBuyTradesTable extends Migration
{
    public function up()
    {
        Schema::table('buy_trades', function (Blueprint $table) {
            $table->string('network')->after('wallet_address')->nullable();
        });
    }

    public function down()
    {
        Schema::table('buy_trades', function (Blueprint $table) {
            $table->dropColumn('network');
        });
    }
}
