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
        Schema::create('erp_job_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('job_order_type')->nullable()->comment('Job work Or Subcontracting');
            $table->unsignedBigInteger('store_id')->nullable();
            $table->unsignedBigInteger('book_id')->nullable();
            $table->string('book_code')->nullable();
            $table->string('document_number')->nullable();
            $table->string('doc_number_type')->nullable();
            $table->string('doc_reset_pattern')->nullable();
            $table->string('doc_prefix')->nullable();
            $table->string('doc_suffix')->nullable();
            $table->string('doc_no')->nullable();
            $table->date('document_date')->nullable();
            $table->string('revision_number')->default(0);
            $table->date('revision_date')->nullable();
            $table->string('reference_number')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->string('vendor_code')->nullable();
            $table->unsignedBigInteger('billing_address')->nullable();
            $table->unsignedBigInteger('shipping_address')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->string('currency_code')->nullable();
            $table->string('document_status')->nullable();
            $table->integer('approval_level')->default(1);
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('payment_term_id')->nullable();
            $table->string('payment_term_code')->nullable();
            $table->double('total_item_value',20,6)->default(0);
            $table->double('total_discount_value',20,6)->default(0);
            $table->double('total_tax_value',20,6)->default(0);
            $table->double('total_expense_value',20,6)->default(0);
            $table->unsignedBigInteger('org_currency_id')->nullable();
            $table->string('org_currency_code')->nullable();
            $table->double('org_currency_exg_rate',20,6)->default(1);
            $table->unsignedBigInteger('comp_currency_id')->nullable();
            $table->string('comp_currency_code')->nullable();
            $table->double('comp_currency_exg_rate',20,6)->default(1);
            $table->unsignedBigInteger('group_currency_id')->nullable();
            $table->string('group_currency_code')->nullable();
            $table->double('group_currency_exg_rate',20,6)->default(0);
            $table->enum('gate_entry_required',['yes','no'])->default('no');
            $table->enum('supp_invoice_required',['yes','no'])->default('no');
            $table->enum('partial_delivery',['yes','no'])->default('no');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('erp_jo_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jo_id')->nullable();
            $table->unsignedBigInteger('pwo_so_mapping_id')->nullable();
            $table->unsignedBigInteger('so_id')->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('item_code')->nullable();
            $table->unsignedBigInteger('hsn_id')->nullable();
            $table->string('hsn_code')->nullable();
            $table->unsignedBigInteger('uom_id')->nullable();
            $table->string('uom_code')->nullable();
            $table->double('order_qty',20,6)->default(0);
            $table->double('grn_qty',20,6)->default(0);
            $table->double('short_close_qty',20,6)->default(0);
            $table->unsignedBigInteger('inventory_uom_id')->nullable();
            $table->string('inventory_uom_code')->nullable();
            $table->double('inventory_uom_qty',20,6)->default(0);
            $table->double('rate',20,6)->default(0);
            $table->double('item_discount_amount',20,6)->default(0);
            $table->double('header_discount_amount',20,6)->default(0);
            $table->double('tax_amount',20,6)->default(0);
            $table->double('expense_amount',20,6)->default(0);
            $table->text('remarks')->nullable();
            $table->date('delivery_date')->nullable();
            $table->timestamps();
        });
        Schema::create('erp_jo_product_attributes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jo_id')->nullable();
            $table->unsignedBigInteger('jo_product_id')->nullable();
            $table->unsignedBigInteger('item_attribute_id')->nullable();
            $table->string('item_code')->nullable();
            $table->string('attribute_name')->nullable();
            $table->string('attribute_value')->nullable();
            $table->timestamps();
        });
        Schema::create('erp_jo_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jo_id')->nullable();
            $table->unsignedBigInteger('so_id')->nullable();
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
        Schema::create('erp_jo_item_attributes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jo_id')->nullable();
            $table->unsignedBigInteger('jo_item_id')->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('item_code')->nullable();
            $table->unsignedBigInteger('item_attribute_id')->nullable();
            $table->string('attribute_name')->nullable();
            $table->string('attribute_value')->nullable();
            $table->timestamps();
        });
        Schema::create('erp_jo_product_delivery', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jo_id')->nullable();
            $table->unsignedBigInteger('jo_product_id')->nullable();
            $table->double('qty',20,6)->default(0);
            $table->double('grn_qty',20,6)->default(0);
            $table->date('delivery_date')->nullable();
            $table->timestamps();
        });
        Schema::create('erp_jo_terms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jo_id')->nullable();
            $table->unsignedBigInteger('term_id')->nullable();
            $table->string('term_code')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
        Schema::create('erp_jo_media', function (Blueprint $table) {
            $table->id();
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->string('uuid')->nullable();
            $table->string('model_name')->nullable();
            $table->string('collection_name')->nullable();
            $table->string('name')->nullable();
            $table->string('file_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('disk')->nullable();
            $table->string('conversions_disk')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->json('manipulations');
            $table->json('custom_properties');
            $table->json('generated_conversions');
            $table->json('responsive_images');
            $table->integer('order_column')->nullable();
            $table->timestamps();
        });
        Schema::create('erp_job_order_ted', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jo_id')->nullable();
            $table->unsignedBigInteger('jo_product_id')->nullable();
            $table->string('ted_type')->nullable();
            $table->string('ted_level')->nullable()->comment('H or D');
            $table->unsignedBigInteger('ted_id')->nullable();
            $table->string('ted_name')->nullable();
            $table->double('assessment_amount',20,6)->default(0);
            $table->double('ted_perc',20,6)->default(0);
            $table->double('ted_amount',20,6)->default(0);
            $table->string('applicable_type')->nullable();
            $table->timestamps();
        });
        Schema::create('erp_jo_bom_mapping', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jo_id')->nullable();
            $table->unsignedBigInteger('jo_product_id')->nullable();
            $table->unsignedBigInteger('so_id')->nullable();
            $table->unsignedBigInteger('bom_id')->nullable();
            $table->unsignedBigInteger('bom_detail_id')->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('item_code')->nullable();
            $table->unsignedBigInteger('uom_id')->nullable();
            $table->json('attributes')->nullable();
            $table->enum('rm_type',['rm','sf'])->default('rm')->index();
            $table->double('bom_qty', 20,6)->default(0);
            $table->double('qty', 20,6)->default(0);
            $table->unsignedBigInteger('station_id')->nullable();
            $table->unsignedBigInteger('section_id')->nullable();
            $table->unsignedBigInteger('sub_section_id')->nullable();
            $table->timestamps();
        });
        Schema::table('erp_pwo_so_mapping', function (Blueprint $table) {
            $table->double('jo_qty',20,6)->default(0)->after('pslip_qty')->comment('Job Order Qty');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_pwo_so_mapping', function (Blueprint $table) {
            $table->dropColumn('jo_qty');
        });
        Schema::dropIfExists('erp_jo_bom_mapping');
        Schema::dropIfExists('erp_job_order_ted');
        Schema::dropIfExists('erp_jo_media');
        Schema::dropIfExists('erp_jo_terms');
        Schema::dropIfExists('erp_jo_product_delivery');
        Schema::dropIfExists('erp_jo_product_attributes');
        Schema::dropIfExists('erp_jo_products');
        Schema::dropIfExists('erp_jo_item_attributes');
        Schema::dropIfExists('erp_jo_items');
        Schema::dropIfExists('erp_job_orders');
    }
};
