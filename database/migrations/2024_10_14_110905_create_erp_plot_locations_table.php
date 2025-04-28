<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('erp_plot_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('land_plot_id')->nullable(); // Foreign key to land_plot
            $table->string('name')->nullable(); // Location name
            $table->decimal('latitude', 10, 8); // Latitude
            $table->decimal('longitude', 11, 8); // Longitude
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('land_plot_id')->references('id')->on('erp_land_plots')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('erp_plot_locations');
    }
};
