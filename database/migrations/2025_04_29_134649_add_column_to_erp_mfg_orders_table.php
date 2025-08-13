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
        Schema::table('erp_mfg_orders', function (Blueprint $table) {
            $table->dropColumn(['sf_item_id','sf_item_attributes','sf_qty']);
        });
        Schema::table('erp_mfg_orders', function (Blueprint $table) {
            $table->boolean('is_last_station')->default(false)->after('station_id')->comment('1=yes, 0=no');
        });
        Schema::table('erp_mo_items', function (Blueprint $table) {
            $table->unsignedBigInteger('old_mo_product_id')->nullable()->after('mo_id');
        });
        if (Schema::hasTable('erp_mo_bom_mapping')) {
            Schema::dropIfExists('erp_mo_bom_mapping');
        }
        if (Schema::hasTable('erp_mo_bom_mapping_history')) {
            Schema::dropIfExists('erp_mo_bom_mapping_history');
        }
        if (Schema::hasTable('erp_mo_production_items')) {
            Schema::dropIfExists('erp_mo_production_items');
        }
        if (Schema::hasTable('erp_mo_production_item_attributes')) {
            Schema::dropIfExists('erp_mo_production_item_attributes');
        }
        if (Schema::hasTable('erp_mo_production_item_locations')) {
            Schema::dropIfExists('erp_mo_production_item_locations');
        }
        if (Schema::hasTable('erp_mo_item_locations')) {
            Schema::dropIfExists('erp_mo_item_locations');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_mo_items', function (Blueprint $table) {
            $table->dropColumn('old_mo_product_id');
        }); 
        Schema::table('erp_mfg_orders', function (Blueprint $table) {
            $table->dropColumn('is_last_station');
        });
    }
};
