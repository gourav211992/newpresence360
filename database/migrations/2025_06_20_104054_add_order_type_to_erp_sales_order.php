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
        Schema::table('erp_sale_orders', function (Blueprint $table) {
            $table->string('order_type', 50) -> default('Order') -> after('document_type');
        });
        Schema::table('erp_sale_orders_history', function (Blueprint $table) {
            $table->string('order_type', 50) -> default('Order') -> after('document_type');
        });

        Schema::table('erp_so_items', function (Blueprint $table) {
            $table->unsignedBigInteger('jo_product_id') -> nullable();
        });
        Schema::table('erp_sale_orders_history', function (Blueprint $table) {
            $table->unsignedBigInteger('jo_product_id') -> nullable();
        });

        Schema::create('erp_so_job_work_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_order_id');
            $table->unsignedBigInteger('so_item_id');
            $table->unsignedBigInteger('bom_detail_id')->nullable();
            $table->unsignedBigInteger('station_id')->nullable();
            $table->enum('rm_type',['rm', 'sf'])->default('rm');
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('item_code')->nullable();
            $table->unsignedBigInteger('uom_id')->nullable();
            $table->double('qty',20,6)->default(0);
            $table->double('consumed_qty',20,6)->default(0);
            $table->double('rate',20,6)->default(0);
            $table->unsignedBigInteger('inventory_uom_id')->nullable();
            $table->string('inventory_uom_code')->nullable();
            $table->double('inventory_uom_qty',20,6)->default(0);
            $table->timestamps();
        });
        Schema::create('erp_so_job_work_item_attributes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_order_id')->nullable();
            $table->unsignedBigInteger('job_work_item_id')->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('item_code')->nullable();
            $table->unsignedBigInteger('item_attribute_id')->nullable();
            $table->string('attribute_name')->nullable();
            $table->unsignedBigInteger('attr_name')->nullable();
            $table->string('attribute_value')->nullable();
            $table->unsignedBigInteger('attr_value')->nullable();
            $table->timestamps();
        });

        Schema::create('erp_so_job_work_items_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id');
            $table->unsignedBigInteger('sale_order_id');
            $table->unsignedBigInteger('so_item_id');
            $table->unsignedBigInteger('bom_detail_id')->nullable();
            $table->unsignedBigInteger('station_id')->nullable();
            $table->enum('rm_type',['rm', 'sf'])->default('rm');
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('item_code')->nullable();
            $table->unsignedBigInteger('uom_id')->nullable();
            $table->double('qty',20,6)->default(0);
            $table->double('consumed_qty',20,6)->default(0);
            $table->double('rate',20,6)->default(0);
            $table->unsignedBigInteger('inventory_uom_id')->nullable();
            $table->string('inventory_uom_code')->nullable();
            $table->double('inventory_uom_qty',20,6)->default(0);
            $table->timestamps();
        });
        Schema::create('erp_so_job_work_item_attributes_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id');
            $table->unsignedBigInteger('sale_order_id');
            $table->unsignedBigInteger('job_work_item_id');
            $table->unsignedBigInteger('item_id');
            $table->string('item_code')->nullable();
            $table->unsignedBigInteger('item_attribute_id')->nullable();
            $table->string('attribute_name')->nullable();
            $table->unsignedBigInteger('attr_name')->nullable();
            $table->string('attribute_value')->nullable();
            $table->unsignedBigInteger('attr_value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_sale_orders', function (Blueprint $table) {
            $table->dropColumn(['order_type']);
        });
        Schema::table('erp_sale_orders_history', function (Blueprint $table) {
            $table->dropColumn(['order_type']);
        });
        Schema::table('erp_so_items', function (Blueprint $table) {
            $table->dropColumn(['jo_product_id']);
        });
        Schema::table('erp_sale_orders_history', function (Blueprint $table) {
            $table->dropColumn(['jo_product_id']);
        });
        
        Schema::dropIfExists('erp_so_job_work_item_attributes_history');
        Schema::dropIfExists('erp_so_job_work_items_history');

        Schema::dropIfExists('erp_so_job_work_item_attributes');
        Schema::dropIfExists('erp_so_job_work_items');
    }
};
