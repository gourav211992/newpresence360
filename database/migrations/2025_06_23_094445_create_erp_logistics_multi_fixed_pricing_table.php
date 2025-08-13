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
        
        Schema::create('erp_logistics_mf_pricing', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('source_state_id')->nullable();
            $table->unsignedBigInteger('source_city_id')->nullable();
            $table->unsignedBigInteger('destination_state_id')->nullable();
            $table->unsignedBigInteger('destination_city_id')->nullable();
            $table->json('vehicle_type_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('status', 50)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_logistics_multi_fixed_pricing');
    }
};
