<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDefaultValueToPurchaseBillQtyInErpMrnDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('erp_mrn_details', function (Blueprint $table) {
            $table->decimal('purchase_bill_qty', 20, 6)->default(0.00)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('erp_mrn_details', function (Blueprint $table) {
            $table->decimal('purchase_bill_qty', 10, 2)->nullable()->change();
        });
    }
}
