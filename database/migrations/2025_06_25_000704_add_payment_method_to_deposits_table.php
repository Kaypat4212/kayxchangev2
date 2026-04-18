<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentMethodToDepositsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('deposits')) {
            return;
        }
        Schema::table('deposits', function (Blueprint $table) {
            if (!Schema::hasColumn('deposits', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('currency');
            }
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