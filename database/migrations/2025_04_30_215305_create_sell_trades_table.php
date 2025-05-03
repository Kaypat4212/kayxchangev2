<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSellTradesTable extends Migration
{
    public function up()
    {
        Schema::create('sell_trades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // Foreign key to users table
            $table->string('name'); // Name of the user
            $table->string('coin'); // The crypto coin being sold
            $table->decimal('usd_amount', 10, 2); // Amount in USD
            $table->decimal('naira_amount', 15, 2); // Converted amount in Naira
            $table->string('wallet_address'); // Wallet address provided by the user
            $table->string('payment_proof')->nullable(); // Proof of payment file path
            $table->string('status')->default('pending'); // Status of the trade (e.g., pending, completed)
            $table->string('account_name'); // Bank account name for the user
            $table->string('account_number'); // Bank account number for the user
            $table->string('bank_name'); // Bank name for the user
            $table->string('payment_method'); // Method of payment (e.g., balance, external)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sell_trades');
    }
}
