<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecoveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('erp_recoveries', function (Blueprint $table) {
            $table->id();
            $table->string('series');
            $table->string('document_no');
            $table->string('land_no');
            $table->string('khasara_no');
            $table->string('area_sqft');
            $table->string('plot_details');
            $table->string('pincode');
            $table->decimal('cost', 15, 2);
            $table->string('customer');
            $table->string('lease_time');
            $table->decimal('lease_cost', 15, 2);
            $table->decimal('bal_lease_cost', 15, 2);
            $table->decimal('received_amount', 15, 2);
            $table->date('date_of_payment');
            $table->string('payment_mode');
            $table->string('reference_no');
            $table->string('bank_name')->nullable();
            $table->string('document')->nullable(); // Store the file path
            $table->string('remarks', 250)->nullable();
            $table->timestamps(); // Created at & Updated at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('erp_recoveries');
    }
}

