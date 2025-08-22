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
        Schema::table('erp_pslip_item_locations', function (Blueprint $table) {
            $table->dropColumn('item_code');
            $table->dropColumn('rack_code');
            $table->dropColumn('bin_code');
            $table->dropColumn('shelf_code');
            $table->dropColumn('store_code');
        });
        Schema::table('erp_pslip_item_locations', function (Blueprint $table) {
            $table->unsignedBigInteger('sub_store_id')->nullable()->index()->after('store_id');
            $table->unsignedBigInteger('station_id')->nullable()->index()->after('sub_store_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_pslip_item_locations', function (Blueprint $table) {
            $table->dropIndex(['sub_store_id']);
            $table->dropColumn('sub_store_id');
            $table->dropIndex(['station_id']);
            $table->dropColumn('station_id');

        });
    }
};
