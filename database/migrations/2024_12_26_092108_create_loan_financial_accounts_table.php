<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('erp_loan_financial_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pro_ledger_id'); // Processing Fee Income Account
            $table->unsignedBigInteger('pro_ledger_group_id');
            $table->unsignedBigInteger('dis_ledger_id'); // Loan Disbursement Account
            $table->unsignedBigInteger('dis_ledger_group_id');
            $table->unsignedBigInteger('int_ledger_id'); // Interest Income Account
            $table->unsignedBigInteger('int_ledger_group_id');
            $table->unsignedBigInteger('wri_ledger_id'); // Loan Writeoff Account
            $table->unsignedBigInteger('wri_ledger_group_id');
            $table->string('status');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('erp_loan_financial_accounts');
    }
};
