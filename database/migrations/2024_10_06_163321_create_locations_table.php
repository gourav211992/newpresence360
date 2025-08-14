<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocationsTable extends Migration
{
    public function up()
    {
        Schema::create('erp_land_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('land_parcel_id')->nullable(); // Foreign key to land_parcel
            $table->string('name')->nullable(); // Location name
            $table->decimal('latitude', 10, 8); // Latitude
            $table->decimal('longitude', 11, 8); // Longitude
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('land_parcel_id')->references('id')->on('erp_land_parcels')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('erp_land_locations');
    }
}

