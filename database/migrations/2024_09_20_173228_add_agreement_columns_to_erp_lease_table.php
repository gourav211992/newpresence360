<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAgreementColumnsToErpLeaseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('erp_leases', function (Blueprint $table) {
            $table->string('agreement_no')->nullable(); // Add agreement_no column
            $table->date('date_of_agreement')->nullable(); // Add date_of_agreement column
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('erp_leases', function (Blueprint $table) {
            $table->dropColumn('agreement_no');
            $table->dropColumn('date_of_agreement');
        });
    }
}
