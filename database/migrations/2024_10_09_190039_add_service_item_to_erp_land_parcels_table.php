<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddServiceItemToErpLandParcelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('erp_land_parcels', function (Blueprint $table) {
            $table->json('service_item')->nullable()->after('remarks'); // Add 'service_item' column as JSON
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
            $table->dropColumn('service_item'); // Drop the 'service_item' column
        });
    }
}
