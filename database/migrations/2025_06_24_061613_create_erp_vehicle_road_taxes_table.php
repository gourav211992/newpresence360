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
    
        
        Schema::create('erp_vehicle_road_taxes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('vehicle_id')->nullable();

            $table->date('road_tax_from')->nullable();
            $table->date('road_tax_to')->nullable();

            $table->decimal('road_tax_amount', 10, 2)->nullable();

            $table->date('road_paid_on')->nullable();

            $table->unsignedBigInteger('attachment_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_vehicle_road_taxes');
    }
};
