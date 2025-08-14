<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSalesPersonToErpCustomersTable extends Migration
{
    public function up()
    {
        Schema::table('erp_customers', function (Blueprint $table) {
            $table->unsignedBigInteger('sales_person_id')->nullable()->after('ledger_id');
        });
    }

    public function down()
    {
        Schema::table('erp_customers', function (Blueprint $table) {
            $table->dropColumn('sales_person_id');
        });
    }
}
