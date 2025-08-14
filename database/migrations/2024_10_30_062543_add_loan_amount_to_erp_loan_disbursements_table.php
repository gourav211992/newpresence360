<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('erp_loan_disbursements', function (Blueprint $table) {
        $table->decimal('loan_amount', 15, 2)->nullable()->after('customer_bank_name');
    });
}

public function down()
{
    Schema::table('erp_loan_disbursements', function (Blueprint $table) {
        $table->dropColumn('loan_amount');
    });
}

};
