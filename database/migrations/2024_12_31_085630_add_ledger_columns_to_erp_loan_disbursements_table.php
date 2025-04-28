<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLedgerColumnsToErpLoanDisbursementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('erp_loan_disbursements', function (Blueprint $table) {
            $table->integer('ledger_id')->nullable()->after('customer_bank_name');
            $table->integer('ledger_group_id')->nullable()->after('ledger_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('erp_loan_disbursements', function (Blueprint $table) {
            $table->dropColumn(['ledger_id', 'ledger_group_id']);
        });
    }
}