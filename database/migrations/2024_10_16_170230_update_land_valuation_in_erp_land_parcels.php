<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateLandValuationInErpLandParcels extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('erp_land_parcels', function (Blueprint $table) {
            // Modify the 'land_valuation' column to have a default value of 0
            $table->decimal('land_valuation', 12, 2)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_land_parcels', function (Blueprint $table) {
            // Revert 'land_valuation' to its previous state (removing default 0)
            $table->decimal('land_valuation', 12, 2)->nullable()->change();
        });
    }
}
