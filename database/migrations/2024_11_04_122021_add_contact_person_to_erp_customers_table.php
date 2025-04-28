<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContactPersonToErpCustomersTable extends Migration
{
    public function up()
    {
        Schema::table('erp_customers', function (Blueprint $table) {
            $table->string('contact_person', 299)->nullable()->after('sales_person_id');
        });
    }

    public function down()
    {
        Schema::table('erp_customers', function (Blueprint $table) {
            $table->dropColumn('contact_person');
        });
    }
}
