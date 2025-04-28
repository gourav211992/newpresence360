<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ErpCreateLandParcelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('erp_land_parcels', function (Blueprint $table) {
            $table->id();
            $table->string('series_id'); // Series foreign key
            $table->string('document_no');
            $table->string('name'); // District, village, pincode
            $table->string('description'); // District, village, pincode
            $table->string('surveyno'); // District, village, pincode
            $table->boolean('status')->default(true); // Status (Active or Inactive)
            $table->string('khasara_no')->nullable();
            $table->decimal('plot_area', 10, 2);
            $table->string('area_unit'); // Acres, hectares
            $table->string('dimension')->nullable();
            $table->decimal('land_valuation', 12, 2)->nullable();
            $table->string('address')->nullable();
            $table->string('district')->nullable();
            $table->string('state')->nullable(); // Dropdown (Agricultural, Commercial, etc.)
            $table->string('country')->nullable(); // Dropdown (Agricultural, Commercial, etc.)
            $table->string('pincode')->nullable(); // Dropdown (Agricultural, Commercial, etc.)
            $table->text('remarks')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('geofence_file')->nullable(); // Geofence file path
            $table->date('handoverdate')->nullable(); // New handover date column
            $table->string('organization_id')->nullable();
            $table->string('user_id')->nullable();
            $table->string('type')->nullable(); // Define the type based on your requirements (string or enum)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('erp_land_parcels');
    }
}

