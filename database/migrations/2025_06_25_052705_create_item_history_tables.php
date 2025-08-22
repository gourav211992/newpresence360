<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // erp_items_history
        Schema::create('erp_items_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('source_id')->nullable();
    
            // Item Identification
            $table->string('item_code_type')->nullable();
            $table->string('item_code')->nullable();
            $table->string('item_initial')->nullable();
            $table->string('item_name')->nullable();
            $table->text('item_remark')->nullable();
    
            // Item Type and Classification
            $table->string('type')->default('Goods');
            $table->string('service_type')->nullable();
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->unsignedBigInteger('subcategory_id')->nullable()->index();
    
            // Units of Measure
            $table->unsignedBigInteger('unit_id')->nullable()->index();
            $table->unsignedBigInteger('uom_id')->nullable()->index();
            $table->unsignedBigInteger('storage_uom_id')->nullable();
            $table->double('storage_uom_conversion', 8, 2)->nullable();
            $table->integer('storage_uom_count')->nullable();
    
            // Storage Details
            $table->string('storage_type')->nullable();
            $table->decimal('storage_weight', 15, 0)->nullable();
            $table->decimal('storage_volume', 15, 0)->nullable();
    
            // Pricing and Currency
            $table->decimal('cost_price', 15, 4)->nullable();
            $table->unsignedBigInteger('cost_price_currency_id')->nullable();
            $table->decimal('sell_price', 10, 2)->nullable();
            $table->unsignedBigInteger('sell_price_currency_id')->nullable();
    
            // Inventory Management
            $table->integer('min_stocking_level')->nullable();
            $table->integer('max_stocking_level')->nullable();
            $table->integer('reorder_level')->nullable();
            $table->integer('minimum_order_qty')->nullable();
            $table->integer('lead_days')->nullable();
            $table->integer('safety_days')->nullable();
            $table->integer('shelf_life_days')->nullable();
    
            // Tolerances
            $table->decimal('po_positive_tolerance', 10, 2)->nullable();
            $table->decimal('po_negative_tolerance', 10, 2)->nullable();
            $table->decimal('so_positive_tolerance', 10, 2)->nullable();
            $table->decimal('so_negative_tolerance', 10, 2)->nullable();
    
            // Flags and Booleans
            $table->boolean('is_serial_no')->default(0);
            $table->boolean('is_batch_no')->default(0);
            $table->boolean('is_expiry')->default(0);
            $table->string('is_inspection')->default('None');
            $table->unsignedBigInteger('inspection_checklist_id')->nullable();
            $table->boolean('is_traded_item')->default(0);
            $table->boolean('is_asset')->default(0);
            $table->unsignedBigInteger('asset_category_id')->nullable();
            $table->integer('expected_life')->nullable();
            $table->string('maintenance_schedule')->nullable();
    
            // BOM (Bill of Materials)
            $table->string('bom_type')->nullable();
    
            // HSN Code and Book Information
            $table->unsignedBigInteger('hsn_id')->nullable()->index();
            $table->integer('book_id')->nullable();
            $table->string('book_code')->nullable()->index();
    
            // Organizational Information
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable();
    
            // Status and Approval
            $table->string('status')->nullable();
            $table->string('document_status')->nullable();
            $table->integer('approval_level')->default(1);
            $table->string('revision_number')->default(0);
            $table->timestamp('revision_date')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
    
            // Timestamps and Soft Deletes
            $table->timestamps();
            $table->softDeletes();
        });
    
        // erp_item_attributes_history
        Schema::create('erp_item_attributes_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('item_id')->nullable()->index();
            $table->unsignedBigInteger('attribute_group_id')->nullable()->index();
            $table->longText('attribute_id')->nullable();
            $table->boolean('required_bom')->default(0);
            $table->boolean('all_checked')->default(0); 
            $table->timestamps();
            $table->softDeletes();
        });
    
        // erp_item_specifications_history
        Schema::create('erp_item_specifications_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('item_id')->nullable()->index();
            $table->unsignedBigInteger('group_id')->nullable()->index();
            $table->unsignedBigInteger('specification_id')->nullable()->index();
            $table->string('specification_name')->nullable();
            $table->string('value')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    
        // erp_item_subtypes_history
        Schema::create('erp_item_subtypes_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('item_id')->nullable()->index();
            $table->unsignedBigInteger('sub_type_id')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });
    
        // erp_alternate_uoms_history
        Schema::create('erp_alternate_uoms_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('item_id')->nullable()->index();
            $table->unsignedBigInteger('uom_id')->nullable()->index();
            $table->decimal('conversion_to_inventory', 10, 2)->nullable();
            $table->decimal('cost_price', 15, 4)->nullable();
            $table->decimal('sell_price', 10, 2)->nullable();
            $table->boolean('is_selling')->default(0)->nullable();
            $table->boolean('is_purchasing')->default(0)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    
        // erp_vendor_items_history
        Schema::create('erp_vendor_items_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('item_id')->nullable()->index();
            $table->unsignedBigInteger('vendor_id')->nullable()->index();
            $table->unsignedBigInteger('uom_id')->nullable()->index(); 
            $table->string('vendor_code')->nullable();
            $table->string('item_code')->nullable()->index();
            $table->string('item_name')->nullable()->index();
            $table->string('part_number')->nullable();
            $table->text('item_details')->nullable();
            $table->decimal('cost_price', 15, 4)->nullable();
            $table->integer('group_id')->nullable();
            $table->integer('company_id')->nullable();
            $table->integer('organization_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    
        // erp_customer_items_history
        Schema::create('erp_customer_items_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('item_id')->nullable()->index();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
             $table->unsignedBigInteger('uom_id')->nullable()->index(); 
            $table->string('customer_code')->nullable();
            $table->string('item_code')->nullable();
            $table->string('item_name')->nullable();
            $table->string('part_number')->nullable();
            $table->text('item_details')->nullable();
            $table->decimal('sell_price', 15, 4)->nullable();
            $table->integer('group_id')->nullable();
            $table->integer('company_id')->nullable();
            $table->integer('organization_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    
        // erp_alternate_items_history
        Schema::create('erp_alternate_items_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('item_id')->nullable()->index();
            $table->string('item_code')->nullable();
            $table->string('item_name')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('erp_items_history');
        Schema::dropIfExists('erp_item_attributes_history');
        Schema::dropIfExists('erp_item_specifications_history');
        Schema::dropIfExists('erp_item_subtypes_history');
        Schema::dropIfExists('erp_alternate_uoms_history');
        Schema::dropIfExists('erp_vendor_items_history');
        Schema::dropIfExists('erp_customer_items_history');
        Schema::dropIfExists('erp_alternate_items_history');
    }
};
