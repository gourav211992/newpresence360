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
        Schema::table('erp_production_work_orders', function (Blueprint $table) {
            $table->string('so_tracking_required')->default('no')->after('station_wise_consumption');
        });
        Schema::table('erp_production_work_orders_history', function (Blueprint $table) {
            $table->string('so_tracking_required')->default('no')->after('station_wise_consumption');
            
        });
        Schema::table('erp_pwo_bom_mapping', function (Blueprint $table) {
            $table->unsignedBigInteger('so_id')->nullable()->after('bom_detail_id');
        });
        Schema::table('erp_pwo_bom_mapping_history', function (Blueprint $table) {
            $table->unsignedBigInteger('so_id')->nullable()->after('bom_detail_id');
        });
        Schema::table('erp_pwo_items', function (Blueprint $table) {
            $table->unsignedBigInteger('so_id')->nullable()->after('pwo_id');
        });
        Schema::table('erp_pwo_items_history', function (Blueprint $table) {
            $table->unsignedBigInteger('so_id')->nullable()->after('pwo_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_pwo_items_history', function (Blueprint $table) {
            $table->dropColumn('so_id');
        });
        Schema::table('erp_pwo_items', function (Blueprint $table) {
            $table->dropColumn('so_id');
        });
        Schema::table('erp_pwo_bom_mapping_history', function (Blueprint $table) {
            $table->dropColumn('so_id');
        });
        Schema::table('erp_pwo_bom_mapping', function (Blueprint $table) {
            $table->dropColumn('so_id');
        });
        Schema::table('erp_production_work_orders_history', function (Blueprint $table) {
            $table->dropColumn('so_tracking_required');
        });
        Schema::table('erp_production_work_orders', function (Blueprint $table) {
            $table->dropColumn('so_tracking_required');
        });
    }
};
