<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLandPlotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('erp_land_plots', function (Blueprint $table) {
            $table->id();
            $table->string('series_id'); // Series foreign key
            $table->string('document_no');
            $table->foreignId('land_id'); // Land foreign key
            $table->decimal('land_size', 10, 2); // Size of land (e.g., acres)
            $table->string('land_location'); // District, village, pincode
            $table->boolean('status')->default(true); // Status (Active or Inactive)
            $table->string('khasara_no')->nullable();
            $table->decimal('plot_area', 10, 2);
            $table->string('area_unit'); // Acres, hectares
            $table->string('dimension')->nullable();
            $table->decimal('plot_valuation', 12, 2)->nullable();
            $table->string('address')->nullable();
            $table->string('pincode')->nullable();
            $table->string('type_of_usage'); // Dropdown (Agricultural, Commercial, etc.)
            $table->text('remarks')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('geofence_file')->nullable(); // Geofence file path

            // Adding the new columns
            $table->string('organization_id');
            $table->string('user_id');
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
        Schema::dropIfExists('erp_land_plots');
    }
}
