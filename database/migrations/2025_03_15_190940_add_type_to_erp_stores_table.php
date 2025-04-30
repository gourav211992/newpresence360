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
        DB::statement("ALTER TABLE `erp_stores` CHANGE `store_location_type` `store_location_type` VARCHAR(50) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `erp_stores` CHANGE `store_location_type` `store_location_type` ENUM('Stock', 'Shop floor', 'Administration', 'Other') NOT NULL");
    }
};
