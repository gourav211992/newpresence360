<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop 'approvalStatus' column
        Schema::table('erp_land_parcels', function ($table) {
            $table->dropColumn('user_id');
            $table->dropColumn('type');
        });

        DB::statement("ALTER TABLE `erp_land_parcels` CHANGE COLUMN `landable_type` `type` VARCHAR(255) NULL");

        }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_land_parcels', function (Blueprint $table) {
            //
        });
    }
};
