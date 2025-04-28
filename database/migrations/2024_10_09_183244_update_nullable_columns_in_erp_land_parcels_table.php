<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateNullableColumnsInErpLandParcelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('erp_land_parcels', function (Blueprint $table) {
            // Update columns to be nullable
            $table->string('description')->nullable()->change();
            $table->string('surveyno')->nullable()->change();
            $table->string('area_unit')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('erp_land_parcels', function (Blueprint $table) {
            // Reverse the changes to make the columns NOT NULL
            $table->string('description')->nullable(false)->change();
            $table->string('surveyno')->nullable(false)->change();
            $table->string('area_unit')->nullable(false)->change();
        });
    }
}
