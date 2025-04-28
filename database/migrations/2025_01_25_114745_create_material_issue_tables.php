<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Helpers\ConstantHelper;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('erp_material_issue_header', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('book_id')->nullable();
            $table->string('book_code')->nullable();
            $table->string('document_number')->nullable();
            $table->enum('doc_number_type', ConstantHelper::DOC_NO_TYPES) -> default(ConstantHelper::DOC_NO_TYPE_MANUAL);
            $table->enum('doc_reset_pattern', ConstantHelper::DOC_RESET_PATTERNS) -> nullable() -> default(NULL);
            $table->string('doc_prefix') -> nullable();
            $table->string('doc_suffix') -> nullable();
            $table->integer('doc_no') -> nullable();
            $table->date('document_date')->nullable();
            $table->string('revision_number')->default(0);
            $table->date('revision_date')->nullable();
            $table->string('reference_number')->nullable();
            
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('department_code');
            $table->unsignedBigInteger('from_store_id');
            $table->string('from_store_code');
            $table->unsignedBigInteger('to_store_id')->nullable();
            $table->string('to_store_code')->nullable();

            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->string('vendor_code')->nullable();
            $table->string('consignee_name')->nullable();
            $table->string('consignment_no')->nullable();
            $table->string('eway_bill_no')->nullable();
            $table->string('transporter_name')->nullable();
            $table->string('vehicle_no')->nullable();

            $table->unsignedBigInteger('billing_address')->nullable();
            $table->unsignedBigInteger('shipping_address')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->string('currency_code')->nullable();
            $table->string('document_status')->nullable();
            $table->integer('approval_level')->default(1)->comment('current approval level');
            $table->text('remarks')->nullable();
            $table->decimal('total_item_value', 15, 2)->default(0.00);
            $table->decimal('total_discount_value', 15, 2)->default(0.00);
            $table->decimal('total_tax_value', 15, 2)->default(0.00);
            $table->decimal('total_expense_value', 15, 2)->default(0.00);
            $table->decimal('total_amount', 15, 2)->default(0.00);

            $table->unsignedBigInteger('org_currency_id')->nullable();
            $table->string('org_currency_code')->nullable();
            $table->decimal('org_currency_exg_rate', 15, 6)->nullable();
            $table->unsignedBigInteger('comp_currency_id')->nullable();
            $table->string('comp_currency_code')->nullable();
            $table->decimal('comp_currency_exg_rate', 15, 6)->nullable();
            $table->unsignedBigInteger('group_currency_id')->nullable();
            $table->string('group_currency_code')->nullable();
            $table->decimal('group_currency_exg_rate', 15, 6)->nullable();
        
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();            
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('erp_mi_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('material_issue_id')->nullable();
            $table->unsignedBigInteger('mi_item_id')->nullable()->comment('erp_mi_item_id');
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('item_code')->nullable();
            $table->string('item_name')->nullable();
            $table->unsignedBigInteger('hsn_id')->nullable();
            $table->string('hsn_code')->nullable();
            $table->unsignedBigInteger('uom_id')->nullable();
            $table->string('uom_code')->nullable();
            $table->decimal('issue_qty',15, 2)->default(0.00);
            $table->unsignedBigInteger('inventory_uom_id')->nullable();
            $table->string('inventory_uom_code')->nullable();
            $table->decimal('inventory_uom_qty',15, 2)->default(0.00);
            $table->decimal('rate', 15, 2)->default(0.00);
            $table->decimal('item_discount_amount', 15, 2)->default(0.00);
            $table->decimal('header_discount_amount', 15, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('item_expense_amount', 15, 2)->default(0.00);
            $table->decimal('header_expense_amount', 15, 2)->default(0.00);
            $table->decimal('total_item_amount', 15, 2)->default(0.00);
            $table->text('remarks')->nullable();
            $table->foreign('material_issue_id')->references('id')->on('erp_material_issue_header')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('erp_mi_item_attributes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('material_issue_id')->nullable();
            $table->unsignedBigInteger('mi_item_id')->nullable();
            $table->unsignedBigInteger('item_attribute_id')->nullable()->comment('use tbl erp_item_attributes');
            $table->string('item_code')->nullable();
            $table->string('attribute_name')->nullable();
            $table->unsignedBigInteger('attr_name')->nullable();
            $table->string('attribute_value')->nullable();
            $table->unsignedBigInteger('attr_value')->nullable();

            $table->foreign('mi_item_id')->references('id')->on('erp_mi_items')->onDelete('cascade');
            $table->foreign('material_issue_id')->references('id')->on('erp_material_issue_header')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('erp_mi_item_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('material_issue_id')->nullable();
            $table->unsignedBigInteger('mi_item_id')->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('item_code')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->string('store_code')->nullable();
            $table->unsignedBigInteger('rack_id')->nullable();
            $table->string('rack_code')->nullable();
            $table->unsignedBigInteger('shelf_id')->nullable();
            $table->string('shelf_code')->nullable();
            $table->unsignedBigInteger('bin_id')->nullable();
            $table->string('bin_code')->nullable();
            $table->enum('type', ['from', 'to']);
            $table->double('quantity', 15, 2)->default(0.00);
            $table->double('inventory_uom_qty', 15, 2)->default(0.00);
            $table->foreign('mi_item_id')->references('id')->on('erp_mi_items')->onDelete('cascade');
            $table->foreign('material_issue_id')->references('id')->on('erp_material_issue_header')->onDelete('cascade');

            $table->foreign('store_id')->references('id')->on('erp_stores')->onDelete('cascade');
            $table->foreign('rack_id')->references('id')->on('erp_racks')->onDelete('cascade');
            $table->foreign('shelf_id')->references('id')->on('erp_shelfs')->onDelete('cascade');
            $table->foreign('bin_id')->references('id')->on('erp_bins')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('erp_material_issue_ted', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('material_issue_id')->nullable();
            $table->unsignedBigInteger('mi_item_id')->nullable();
            $table->enum('ted_type', ['Tax', 'Expense', 'Discount'])->comment('Tax, Expense, Discount');
            $table->enum('ted_level', ['H', 'D'])->comment('H or D');
            $table->unsignedBigInteger('ted_id')->nullable();
            $table->string('ted_group_code')->nullable();
            $table->string('ted_name')->nullable();
            $table->decimal('assessment_amount',15, 2)->default(0.00);
            $table->decimal('ted_percentage',15, 2)->default(0.00)->comment('TED Percentage');
            $table->decimal('ted_amount',15, 2)->default(0.00)->comment('TED Amount');
            $table->string('applicable_type')->nullable()->comment('Deduction, Collection');
            $table->foreign('material_issue_id')->references('id')->on('erp_material_issue_header')->onDelete('cascade');
            $table->foreign('mi_item_id')->references('id')->on('erp_mi_items')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('erp_material_issue_media', function (Blueprint $table) {
            $table->id();
            $table->morphs('model');
            $table->uuid()->nullable()->unique();
            $table->string('model_name');
            $table->string('collection_name');
            $table->string('name');
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->string('disk');
            $table->string('conversions_disk')->nullable();
            $table->unsignedBigInteger('size');
            $table->json('manipulations')->nullable();
            $table->json('custom_properties')->nullable();
            $table->json('generated_conversions')->nullable();
            $table->json('responsive_images')->nullable();
            $table->unsignedInteger('order_column')->nullable()->index();
            $table->nullableTimestamps();
        });


        //History
        Schema::create('erp_material_issue_header_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('book_id')->nullable();
            $table->string('book_code')->nullable();
            $table->string('document_number')->nullable();
            $table->enum('doc_number_type', ConstantHelper::DOC_NO_TYPES) -> default(ConstantHelper::DOC_NO_TYPE_MANUAL);
            $table->enum('doc_reset_pattern', ConstantHelper::DOC_RESET_PATTERNS) -> nullable() -> default(NULL);
            $table->string('doc_prefix') -> nullable();
            $table->string('doc_suffix') -> nullable();
            $table->integer('doc_no') -> nullable();
            $table->date('document_date')->nullable();
            $table->string('revision_number')->default(0);
            $table->date('revision_date')->nullable();
            $table->string('reference_number')->nullable();
            
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('department_code');
            $table->unsignedBigInteger('from_store_id');
            $table->string('from_store_code');
            $table->unsignedBigInteger('to_store_id')->nullable();
            $table->string('to_store_code')->nullable();

            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->string('vendor_code')->nullable();
            $table->string('consignee_name')->nullable();
            $table->string('consignment_no')->nullable();
            $table->string('eway_bill_no')->nullable();
            $table->string('transporter_name')->nullable();
            $table->string('vehicle_no')->nullable();

            $table->unsignedBigInteger('billing_address')->nullable();
            $table->unsignedBigInteger('shipping_address')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->string('currency_code')->nullable();
            $table->string('document_status')->nullable();
            $table->integer('approval_level')->default(1)->comment('current approval level');
            $table->text('remarks')->nullable();
            $table->decimal('total_item_value', 15, 2)->default(0.00);
            $table->decimal('total_discount_value', 15, 2)->default(0.00);
            $table->decimal('total_tax_value', 15, 2)->default(0.00);
            $table->decimal('total_expense_value', 15, 2)->default(0.00);
            $table->decimal('total_amount', 15, 2)->default(0.00);

            $table->unsignedBigInteger('org_currency_id')->nullable();
            $table->string('org_currency_code')->nullable();
            $table->decimal('org_currency_exg_rate', 15, 6)->nullable();
            $table->unsignedBigInteger('comp_currency_id')->nullable();
            $table->string('comp_currency_code')->nullable();
            $table->decimal('comp_currency_exg_rate', 15, 6)->nullable();
            $table->unsignedBigInteger('group_currency_id')->nullable();
            $table->string('group_currency_code')->nullable();
            $table->decimal('group_currency_exg_rate', 15, 6)->nullable();
        
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();            
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('erp_mi_items_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('material_issue_id')->nullable();
            $table->unsignedBigInteger('mi_item_id')->nullable()->comment('erp_mi_item_id');
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('item_code')->nullable();
            $table->string('item_name')->nullable();
            $table->unsignedBigInteger('hsn_id')->nullable();
            $table->string('hsn_code')->nullable();
            $table->unsignedBigInteger('uom_id')->nullable();
            $table->string('uom_code')->nullable();
            $table->decimal('issue_qty',15, 2)->default(0.00);
            $table->unsignedBigInteger('inventory_uom_id')->nullable();
            $table->string('inventory_uom_code')->nullable();
            $table->decimal('inventory_uom_qty',15, 2)->default(0.00);
            $table->decimal('rate', 15, 2)->default(0.00);
            $table->decimal('item_discount_amount', 15, 2)->default(0.00);
            $table->decimal('header_discount_amount', 15, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('item_expense_amount', 15, 2)->default(0.00);
            $table->decimal('header_expense_amount', 15, 2)->default(0.00);
            $table->decimal('total_item_amount', 15, 2)->default(0.00);
            $table->text('remarks')->nullable();
            $table->foreign('material_issue_id')->references('id')->on('erp_material_issue_header_history')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('erp_mi_item_attributes_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('material_issue_id')->nullable();
            $table->unsignedBigInteger('mi_item_id')->nullable();
            $table->unsignedBigInteger('item_attribute_id')->nullable()->comment('use tbl erp_item_attributes');
            $table->string('item_code')->nullable();
            $table->string('attribute_name')->nullable();
            $table->unsignedBigInteger('attr_name')->nullable();
            $table->string('attribute_value')->nullable();
            $table->unsignedBigInteger('attr_value')->nullable();

            $table->foreign('mi_item_id')->references('id')->on('erp_mi_items_history')->onDelete('cascade');
            $table->foreign('material_issue_id')->references('id')->on('erp_material_issue_header_history')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('erp_mi_item_locations_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('material_issue_id')->nullable();
            $table->unsignedBigInteger('mi_item_id')->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('item_code')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->string('store_code')->nullable();
            $table->unsignedBigInteger('rack_id')->nullable();
            $table->string('rack_code')->nullable();
            $table->unsignedBigInteger('shelf_id')->nullable();
            $table->string('shelf_code')->nullable();
            $table->unsignedBigInteger('bin_id')->nullable();
            $table->string('bin_code')->nullable();
            $table->enum('type', ['from', 'to']);
            $table->double('quantity', 15, 2)->default(0.00);
            $table->double('inventory_uom_qty', 15, 2)->default(0.00);
            $table->foreign('mi_item_id')->references('id')->on('erp_mi_items_history')->onDelete('cascade');
            $table->foreign('material_issue_id')->references('id')->on('erp_material_issue_header_history')->onDelete('cascade');

            $table->foreign('store_id')->references('id')->on('erp_stores')->onDelete('cascade');
            $table->foreign('rack_id')->references('id')->on('erp_racks')->onDelete('cascade');
            $table->foreign('shelf_id')->references('id')->on('erp_shelfs')->onDelete('cascade');
            $table->foreign('bin_id')->references('id')->on('erp_bins')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('erp_material_issue_ted_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('material_issue_id')->nullable();
            $table->unsignedBigInteger('mi_item_id')->nullable();
            $table->enum('ted_type', ['Tax', 'Expense', 'Discount'])->comment('Tax, Expense, Discount');
            $table->enum('ted_level', ['H', 'D'])->comment('H or D');
            $table->unsignedBigInteger('ted_id')->nullable();
            $table->string('ted_group_code')->nullable();
            $table->string('ted_name')->nullable();
            $table->decimal('assessment_amount',15, 2)->default(0.00);
            $table->decimal('ted_percentage',15, 2)->default(0.00)->comment('TED Percentage');
            $table->decimal('ted_amount',15, 2)->default(0.00)->comment('TED Amount');
            $table->string('applicable_type')->nullable()->comment('Deduction, Collection');
            $table->foreign('material_issue_id')->references('id')->on('erp_material_issue_header_history')->onDelete('cascade');
            $table->foreign('mi_item_id')->references('id')->on('erp_mi_items_history')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_mi_item_attributes');
        Schema::dropIfExists('erp_mi_item_attributes_history');
        Schema::dropIfExists('erp_mi_item_locations');
        Schema::dropIfExists('erp_mi_item_locations_history');
        Schema::dropIfExists('erp_material_issue_ted');
        Schema::dropIfExists('erp_material_issue_ted_history');
        Schema::dropIfExists('erp_mi_items');
        Schema::dropIfExists('erp_mi_items_history');
        Schema::dropIfExists('erp_material_issue_header');
        Schema::dropIfExists('erp_material_issue_header_history');
        Schema::dropIfExists('erp_material_issue_media');
    }
};
