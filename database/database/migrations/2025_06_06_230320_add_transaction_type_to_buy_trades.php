<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTransactionTypeToBuyTrades extends Migration
{
    public function up()
    {
        Schema::table('buy_trades', function (Blueprint $table) {
            $table->string('transaction_type')->default('buy');
        });
    }

    public function down()
    {
        Schema::table('buy_trades', function (Blueprint $table) {
            $table->dropColumn('transaction_type');
        });
    }
}