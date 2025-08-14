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
        Schema::create('erp_land_lease_other_charges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lease_id');
            $table->unsignedBigInteger('land_parcel_id')->nullable();
            $table->unsignedBigInteger('land_plot_id')->nullable();
            $table->string('name')->nullable();
            $table->integer('percentage')->nullable();
            $table->decimal('value', 15, 2)->nullable()->default(0.00);
            $table->decimal('total_other_amount', 15, 2)->nullable()->default(0.00);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('land_lease_other_charges');
    }
};
