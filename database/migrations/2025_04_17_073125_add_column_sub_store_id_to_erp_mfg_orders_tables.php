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
            $table->unsignedBigInteger('sub_store_id')->nullable()->after('store_id');
        });
        Schema::table('erp_mfg_orders_history', function (Blueprint $table) {
            $table->unsignedBigInteger('sub_store_id')->nullable()->after('store_id');
        });
        Schema::table('erp_mo_production_item_locations', function (Blueprint $table) {
            $table->unsignedBigInteger('sub_store_id')->nullable()->after('store_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_mo_production_item_locations', function (Blueprint $table) {
            $table->dropColumn('sub_store_id');
        });
        Schema::table('erp_mfg_orders_history', function (Blueprint $table) {
            $table->dropColumn('sub_store_id');
        });
        Schema::table('erp_mfg_orders', function (Blueprint $table) {
            $table->dropColumn('sub_store_id');
        });
    }
};
