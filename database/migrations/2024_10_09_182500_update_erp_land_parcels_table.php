<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateErpLandParcelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('erp_land_parcels', function (Blueprint $table) {
            // Add a JSON column for attachments
            $table->json('attachments')->nullable()->after('remarks');
            
            // Drop the geofence_file column
            $table->dropColumn('geofence_file');
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
            // Reverse the changes: drop the attachments column and re-add geofence_file
            $table->dropColumn('attachments');
            
            // Re-add the geofence_file column
            $table->string('geofence_file')->nullable()->after('longitude');
        });
    }
}
