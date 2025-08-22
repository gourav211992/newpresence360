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
        Schema::table('erp_mrn_item_locations', function (Blueprint $table) {
            $table->string('packet_name', 291)->nullable()->after('item_id');
            $table->string('packet_number', 291)->nullable()->after('packet_name');
            $table->string('storage_number', 291)->nullable()->after('packet_number');
            $table->unsignedBigInteger('sub_store_id')->nullable()->after('store_id');
            $table->unsignedBigInteger('wh_detail_id')->nullable()->after('sub_store_id')->index();
            $table->string('status', 191)->nullable()->after('inventory_uom_qty')->index();
        });

        Schema::table('erp_mrn_item_location_histories', function (Blueprint $table) {
            $table->string('packet_name', 291)->nullable()->after('item_id');
            $table->string('packet_number', 291)->nullable()->after('packet_name');
            $table->string('storage_number', 291)->nullable()->after('packet_number');
            $table->unsignedBigInteger('sub_store_id')->nullable()->after('store_id');
            $table->unsignedBigInteger('wh_detail_id')->nullable()->after('sub_store_id')->index();
            $table->decimal('inventory_uom_qty', 20,6)->nullable()->after('quantity');
            $table->string('status', 191)->nullable()->after('inventory_uom_qty')->index();
        });

        Schema::create('stock_ledger_storage_points', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_ledger_id')->nullable()->index();
            $table->unsignedBigInteger('item_id')->nullable()->index();
            $table->string('packet_name', 291)->nullable()->index();
            $table->string('packet_number', 291)->nullable()->index();
            $table->string('storage_number', 291)->nullable()->index();
            $table->unsignedBigInteger('wh_detail_id')->nullable()->index();
            $table->unsignedBigInteger('store_id')->nullable()->index();
            $table->unsignedBigInteger('sub_store_id')->nullable()->index();
            $table->double('quantity', 20,6)->default(0);
            $table->string('status', 191)->nullable()->index();
            $table->timestamps();
        });

        Schema::table('erp_items', function (Blueprint $table) {
            $table->float('storage_weight', 15, 4)->nullable()->comment('In KG')->after('storage_uom_conversion');
            $table->float('storage_volume', 15, 4)->nullable()->comment('In CUM')->after('storage_weight');
            $table->tinyInteger('is_inspection')->default(0)->after('storage_volume');
        });

        Schema::table('erp_sub_stores', function (Blueprint $table) {
            $table->tinyInteger('is_warehouse_required')->default(0)->after('station_wise_consumption');
        });

        Schema::table('stock_ledger', function (Blueprint $table) {
            $table->decimal('hold_qty', 15,2)->default(0.00)->after('reserved_qty');
        });

        Schema::table('erp_mrn_details', function (Blueprint $table) {
            $table->tinyInteger('is_inspection')->default(0)->after('header_exp_amount');
        });

        Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
            $table->tinyInteger('is_inspection')->default(0)->after('header_exp_amount');
        });

        Schema::table('erp_mrn_headers', function (Blueprint $table) {
            $table->tinyInteger('is_warehouse_required')->default(0)->after('bill_to_follow');
        });

        Schema::table('erp_mrn_header_histories', function (Blueprint $table) {
            $table->tinyInteger('is_warehouse_required')->default(0)->after('bill_to_follow');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_mrn_header_histories', function (Blueprint $table) {
            $table->dropColumn(['is_warehouse_required']);
        });
        Schema::table('erp_mrn_headers', function (Blueprint $table) {
            $table->dropColumn(['is_warehouse_required']);
        });
        Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
            $table->dropColumn(['is_inspection']);
        });
        Schema::table('erp_mrn_details', function (Blueprint $table) {
            $table->dropColumn(['is_inspection']);
        });
        Schema::table('stock_ledger', function (Blueprint $table) {
            $table->dropColumn(['hold_qty']);
        });
        Schema::table('erp_sub_stores', function (Blueprint $table) {
            $table->dropColumn(['is_warehouse_required']);
        });
        Schema::table('erp_items', function (Blueprint $table) {
            $table->dropColumn(['storage_weight', 'storage_volume', 'is_inspection']);
        });
        Schema::dropIfExists('stock_ledger_storage_points');
        Schema::table('erp_mrn_item_location_histories', function (Blueprint $table) {
            $table->dropColumn(['packet_name', 'packet_number', 'storage_number', 'sub_store_id', 'wh_detail_id', 'inventory_uom_qty', 'status']);
        });
        Schema::table('erp_mrn_item_locations', function (Blueprint $table) {
            $table->dropColumn(['packet_name', 'packet_number', 'storage_number', 'sub_store_id', 'wh_detail_id', 'status']);
        });
    }
};
