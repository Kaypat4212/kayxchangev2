<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentMethodToDepositsTable extends Migration
{
    public function up()
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('currency');
            $table->string('status')->default('pending')->change(); // Change enum to string
        });
    }

    public function down()
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->dropColumn('payment_method');
            // If reverting, you may need to handle the status column differently
            $table->string('status')->default('pending')->change();
        });
    }
}