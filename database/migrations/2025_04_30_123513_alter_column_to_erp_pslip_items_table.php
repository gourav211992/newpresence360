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
        Schema::table('erp_production_slips', function (Blueprint $table) {
            $table->unsignedBigInteger('mo_id')->nullable()->after('company_id');
            $table->unsignedBigInteger('station_id')->nullable()->after('mo_id');
            $table->boolean('is_last_station')->default(false)->after('station_id');
        });
        Schema::table('erp_pslip_items', function (Blueprint $table) {
            $table->dropColumn('order_id');
        });
        Schema::table('erp_pslip_items', function (Blueprint $table) {
            $table->unsignedBigInteger('so_id')->nullable()->after('item_id');
        });
        Schema::table('erp_mo_products', function (Blueprint $table) {
            $table->dropColumn('order_id');
        });
        Schema::table('erp_mo_products', function (Blueprint $table) {
            $table->unsignedBigInteger('so_id')->nullable()->after('pwo_mapping_id');
            $table->unsignedBigInteger('so_item_id')->nullable()->after('so_id');
        });
        Schema::table('erp_pslip_bom_consumptions', function (Blueprint $table) {
            $table->unsignedBigInteger('so_item_id')->nullable()->after('so_id');
            $table->enum('rm_type',['rm','sf'])->default('rm')->index()->after('attributes');
            $table->double('inventory_uom_qty',20,6)->default(0)->after('consumption_qty');
        });
        Schema::table('erp_pwo_station_consumptions', function (Blueprint $table) {
            $table->dropColumn('mo_value');
        });
        Schema::table('erp_pwo_station_consumptions', function (Blueprint $table) {
            $table->double('pslip_qty',20,6)->default(0)->after('mo_product_qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_production_slips', function (Blueprint $table) {
            $table->dropColumn(['mo_id','station_id','is_last_station']);
        });
        Schema::table('erp_pwo_station_consumptions', function (Blueprint $table) {
            $table->double('mo_value',20,6)->default(0)->after('mo_product_qty');
        });
        Schema::table('erp_pwo_station_consumptions', function (Blueprint $table) {
            $table->dropColumn('pslip_qty');
        });
        Schema::table('erp_pslip_bom_consumptions', function (Blueprint $table) {
            $table->dropColumn('so_item_id');
            $table->dropIndex(['rm_type']);
            $table->dropColumn('rm_type');
            $table->dropColumn('inventory_uom_qty');
        });
        Schema::table('erp_pslip_items', function (Blueprint $table) {
            $table->dropColumn('so_id');
        });
        Schema::table('erp_pslip_items', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->nullable()->after('customer_id');
        });
        Schema::table('erp_mo_products', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->nullable()->after('pwo_mapping_id');
        });
        Schema::table('erp_mo_products', function (Blueprint $table) {
            $table->dropColumn('so_id');
            $table->dropColumn('so_item_id');
        });
    }
};
