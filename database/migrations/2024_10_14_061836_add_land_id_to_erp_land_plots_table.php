<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('erp_land_plots', function (Blueprint $table) {
            // Add foreign key constraint to land_id column
            $table->foreign('land_id')->references('id')->on('erp_land_parcels')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('erp_land_plots', function (Blueprint $table) {
            $table->dropForeign(['land_id']);
        });
    }
};
