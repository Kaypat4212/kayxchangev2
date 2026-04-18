<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIpAddressToBuyTradesTable extends Migration
{
    public function up()
    {
        Schema::table('buy_trades', function (Blueprint $table) {
            $table->string('ip_address')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('buy_trades', function (Blueprint $table) {
            $table->dropColumn('ip_address');
        });
    }
}