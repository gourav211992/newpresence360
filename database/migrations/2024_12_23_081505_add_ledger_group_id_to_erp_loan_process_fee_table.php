<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLedgerGroupIdToErpLoanProcessFeeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('erp_loan_process_fee', function (Blueprint $table) {
            $table->string('ledger_group_id')->nullable()->after('ledger_id'); // Replace 'existing_column' with the appropriate column name
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('erp_loan_process_fee', function (Blueprint $table) {
            $table->dropColumn('ledger_group_id');
        });
    }
}
