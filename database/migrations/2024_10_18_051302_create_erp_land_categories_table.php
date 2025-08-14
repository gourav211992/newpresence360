<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateErpLandCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('erp_land_categories', function (Blueprint $table) {
            $table->id(); // id (Primary Key)
            $table->string('category_name'); // category_name
            $table->boolean('status')->default(1); // status (default active)
            $table->timestamps(); // created_at, updated_at

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('erp_land_categories');
    }
}
