<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLandsTable extends Migration
{
    public function up()
    {
        Schema::create('erp_lands', function (Blueprint $table) {
            $table->id();
            $table->string('series');
            $table->string('land_no');
            $table->string('plot_no');
            $table->string('khasara_no');
            $table->float('area');
            $table->string('dimension');
            $table->string('address');
            $table->string('pincode', 6);
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('cost', 15, 2);
            $table->enum('status', ['active', 'inactive']);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('erp_lands');
    }
}

