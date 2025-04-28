<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('erp_loan_disbursements', function (Blueprint $table) {
        $table->date('payment_date')->nullable();
        $table->unsignedBigInteger('bank_details_id')->nullable();
        $table->string('payment_mode')->nullable();
        $table->string('payment_ref_no')->nullable();
        $table->string('customer_account_number')->nullable();
        $table->string('customer_bank_name')->nullable();
    });
}

public function down()
{
    Schema::table('erp_loan_disbursements', function (Blueprint $table) {
        $table->dropColumn([
            'payment_date',
            'bank_details_id',
            'payment_mode',
            'payment_ref_no',
            'customer_account_number',
            'customer_bank_name'
        ]);
    });
}

};
