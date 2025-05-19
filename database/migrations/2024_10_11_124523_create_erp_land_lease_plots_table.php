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
        Schema::create('erp_land_lease_plots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lease_id');
            $table->unsignedBigInteger('land_parcel_id')->nullable();
            $table->unsignedBigInteger('land_plot_id')->nullable();
            $table->string('property_type')->nullable();
            $table->decimal('lease_amount', 15, 2)->nullable()->default(0.00);
            $table->decimal('other_charges', 15, 2)->nullable()->default(0.00);
            $table->decimal('total_amount', 15, 2)->nullable()->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_land_lease_plots');
    }
};
