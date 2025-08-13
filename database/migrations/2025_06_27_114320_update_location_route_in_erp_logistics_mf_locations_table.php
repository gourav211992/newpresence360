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
        Schema::table('erp_logistics_mf_locations', function (Blueprint $table) {
             $table->dropColumn(['state_id', 'city_id']);

            // Add new column
            $table->unsignedBigInteger('location_route_id')->nullable()->after('multi_fixed_pricing_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_logistics_mf_locations', function (Blueprint $table) {
              $table->unsignedBigInteger('state_id')->nullable();
              $table->unsignedBigInteger('city_id')->nullable();

            // Drop the new column
            $table->dropColumn('location_route_id');
        });
    }
};
