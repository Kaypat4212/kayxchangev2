<?php

// database/migrations/xxxx_xx_xx_create_buy_trades_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuyTradesTable extends Migration
{
    public function up()
    {
        Schema::create('buy_trades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // To know who submitted
            $table->decimal('usd_amount', 10, 2);
            $table->decimal('naira_amount', 15, 2);
            $table->string('transaction_type');
            $table->string('wallet_address')->nullable();
            $table->string('payment_proof')->nullable();
            $table->string('coin')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('buy_trades');
    }
}
