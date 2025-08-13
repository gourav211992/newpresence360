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
            $table->unsignedBigInteger('sub_store_id')->nullable()->after('store_id');
            $table->unsignedBigInteger('shift_id')->nullable()->after('sub_store_id');
        });
        Schema::table('erp_pslip_items', function (Blueprint $table) {
            $table->dropColumn([
                'production_bom_id',
                'inventory_uom_code',
                'hsn_id',
                'hsn_code',
                'item_discount_amount',
                'header_discount_amount',
                'tax_amount',
                'item_expense_amount',
                'header_expense_amount',
                'total_item_amount'
            ]);
        });
        Schema::table('erp_pslip_items', function (Blueprint $table) {
            $table->unsignedBigInteger('mo_product_id')->nullable()->after('pslip_id');
            $table->unsignedBigInteger('station_id')->nullable()->after('mo_product_id');
            $table->unsignedBigInteger('sub_store_id')->nullable()->after('store_id');
        });
        Schema::table('erp_mo_products', function (Blueprint $table) {
            $table->double('pslip_qty',20,6)->default(0)->after('qty');
            $table->double('short_closed_qty',20,6)->default(0)->after('pslip_qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_production_slips', function (Blueprint $table) {
            $table->dropColumn([
                'sub_store_id',
                'shift_id'
            ]);
        });
        Schema::table('erp_pslip_items', function (Blueprint $table) {
            $table->unsignedBigInteger('production_bom_id')->nullable();
            $table->string('inventory_uom_code')->nullable();
            $table->unsignedBigInteger('hsn_id')->nullable();
            $table->string('hsn_code')->nullable();
            $table->decimal('item_discount_amount', 20, 4)->default(0);
            $table->decimal('header_discount_amount', 20, 4)->default(0);
            $table->decimal('tax_amount', 20, 4)->default(0);
            $table->decimal('item_expense_amount', 20, 4)->default(0);
            $table->decimal('header_expense_amount', 20, 4)->default(0);
            $table->decimal('total_item_amount', 20, 4)->default(0);
        });
        Schema::table('erp_pslip_items', function (Blueprint $table) {
            $table->dropColumn([
                'sub_store_id',
                'mo_product_id',
                'station_id'
            ]);
        });        
        Schema::table('erp_mo_products', function (Blueprint $table) {
            $table->dropColumn([
                'pslip_qty',
                'short_closed_qty'
            ]);
        });        
    }
};
