<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeletedAtToLoanTables extends Migration
{
    public function up()
    {
        Schema::table('erp_loan_assessment', function (Blueprint $table) {
            $table->softDeletes(); // Adds a deleted_at column
        });

        Schema::table('erp_loan_approval', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('erp_loan_process_fee', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('erp_legal_doc', function (Blueprint $table) {
            $table->softDeletes();
        });
        
        Schema::table('erp_loan_accept', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('erp_loan_assessment', function (Blueprint $table) {
            $table->dropSoftDeletes(); // Drops the deleted_at column
        });

        Schema::table('erp_loan_approval', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('erp_loan_process_fee', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('erp_legal_doc', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        
        Schema::table('erp_loan_accept', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}
