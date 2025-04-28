<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('erp_loan_disbursements', function (Blueprint $table) {
        $table->string('approve_doc')->nullable();
        $table->text('approve_remarks')->nullable();
        $table->string('assess_doc')->nullable();
        $table->text('assess_remarks')->nullable();
        $table->string('reject_doc')->nullable();
        $table->text('reject_remarks')->nullable();
    });
}

public function down()
{
    Schema::table('erp_loan_disbursements', function (Blueprint $table) {
        $table->dropColumn(['approve_doc', 'approve_remarks','assess_doc', 'assess_remarks', 'reject_doc', 'reject_remarks']);
    });
}

};
