<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCusReceivableColumnsToErpHomeLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('erp_home_loans', function (Blueprint $table) {
            $table->integer('cus_receivable_ledgerid')->nullable();
            $table->integer('cus_receivable_ledgergroup')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('erp_home_loans', function (Blueprint $table) {
            $table->dropColumn(['cus_receivable_ledgerid', 'cus_receivable_ledgergroup']);
        });
    }
}
