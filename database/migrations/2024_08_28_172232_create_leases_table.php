<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeasesTable extends Migration
{
    public function up()
    {
        Schema::create('erp_leases', function (Blueprint $table) {
            $table->id();
            $table->string('series');
            $table->string('lease_no');
            $table->string('land_no');
            $table->string('khasara_no');
            $table->decimal('area_sqft', 10, 2);
            $table->string('plot_details');
            $table->string('pincode', 6);
            $table->decimal('cost', 10, 2);
            $table->string('customer');
            $table->integer('lease_time');
            $table->decimal('lease_cost', 10, 2);
            $table->string('period_type');
            $table->integer('repayment_period');
            $table->decimal('installment_cost', 10, 2);
            $table->string('document')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('erp_leases');
    }
}

