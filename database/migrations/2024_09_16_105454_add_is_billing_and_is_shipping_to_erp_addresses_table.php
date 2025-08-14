<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsBillingAndIsShippingToErpAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('erp_addresses', function (Blueprint $table) {
            $table->boolean('is_billing')->default(false)->index()->after('fax_number'); 
            $table->boolean('is_shipping')->default(false)->index()->after('is_billing'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('erp_addresses', function (Blueprint $table) {
            $table->dropColumn('is_billing'); 
            $table->dropColumn('is_shipping'); 
        });
    }
}
