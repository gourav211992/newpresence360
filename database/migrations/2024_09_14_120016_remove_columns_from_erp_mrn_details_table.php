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
        Schema::table('erp_mrn_details', function (Blueprint $table) {
            $table->dropColumn('order_qty_inventory_uom');
            $table->dropColumn('receipt_qty_inventory_uom');
            $table->dropColumn('accepted_qty_inventory_uom');
            $table->dropColumn('rejected_qty_inventory_uom');
            $table->decimal('inventory_uom_qty', 10, 2)->nullable()->after('inventory_uom_id');

        });

        Schema::table('erp_mrn_item_locations', function (Blueprint $table) {
            $table->decimal('inventory_uom_qty', 10, 2)->nullable()->after('quantity');
        });

        Schema::table('erp_mrn_item_location_histories', function (Blueprint $table) {
            $table->decimal('inventory_uom_qty', 10, 2)->nullable()->after('quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_mrn_item_location_histories', function (Blueprint $table) {
            $table->dropColumn('inventory_uom_qty');
        });

        Schema::table('erp_mrn_item_locations', function (Blueprint $table) {
            $table->dropColumn('inventory_uom_qty');
        });

        Schema::table('erp_mrn_details', function (Blueprint $table) {
            $table->decimal('order_qty_inventory_uom', 10, 2)->nullable();
            $table->decimal('receipt_qty_inventory_uom', 10, 2)->nullable();
            $table->decimal('accepted_qty_inventory_uom', 10, 2)->nullable(); 
            $table->decimal('rejected_qty_inventory_uom', 10, 2)->nullable();
            $table->dropColumn('inventory_uom_qty');
        });
    }
};
