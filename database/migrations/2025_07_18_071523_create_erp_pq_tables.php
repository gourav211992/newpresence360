<?php

use App\Helpers\ConstantHelper;
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
        //
        Schema::create('erp_pq_headers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('book_id')->nullable();  
            $table->string('book_code')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->unsignedBigInteger('sub_store_id')->nullable();
            $table->string('store_code')->nullable();
            $table->string('sub_store_code')->nullable();
            $table->enum('doc_number_type', ConstantHelper::DOC_NO_TYPES) -> default(ConstantHelper::DOC_NO_TYPE_MANUAL);
            $table->enum('doc_reset_pattern', ConstantHelper::DOC_RESET_PATTERNS) -> nullable() -> default(NULL);
            $table->string('doc_prefix') -> nullable();
            $table->string('doc_suffix') -> nullable();
            $table->integer('doc_no') -> nullable();
            $table->string('document_number')->nullable();
            $table->date('document_date')->nullable();
            $table->date('due_date')->nullable();
            $table->string('document_status')->nullable();
            $table->string('revision_number')->nullable();
            $table->date('revision_date')->nullable();
            $table->integer('approval_level')->default(1);
            $table->string('reference_number')->nullable();
            $table->unsignedBigInteger('billing_address')->nullable();
            $table->unsignedBigInteger('shipping_address')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->string('currency_code')->nullable();
            $table->unsignedBigInteger('org_currency_id')->nullable();
            $table->string('org_currency_code')->nullable();
            $table->decimal('org_currency_exg_rate', 15, 6)->nullable();
            $table->unsignedBigInteger('comp_currency_id')->nullable();
            $table->string('comp_currency_code')->nullable();
            $table->decimal('comp_currency_exg_rate', 15, 6)->nullable();
            $table->unsignedBigInteger('group_currency_id')->nullable();
            $table->string('group_currency_code')->nullable();
            $table->decimal('group_currency_exg_rate', 15, 6)->nullable();
            $table->decimal('total_item_count', 15, 2)->nullable();
            $table->string('instructions')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->string('vendor_name')->nullable();
            $table->string('vendor_email')->nullable();
            $table->string('vendor_phone')->nullable();
            $table->string('vendor_gstin')->nullable();
            $table->string('consignee_name')->nullable();
            $table->unsignedBigInteger('payment_terms_id')->nullable();
            $table->string('payment_terms_code')->nullable();
            $table->text('remarks')->nullable();
            $table->decimal('total_quotation_value', 15, 2)->default(0.00);
            $table->decimal('total_tax_value', 15, 2)->default(0.00);
            $table->decimal('total_discount_value', 15, 2)->default(0.00);
            $table->decimal('total_expense_value', 15, 2)->default(0.00);
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            // Example foreign key, adjust as needed
            // $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
        });
        Schema::create('erp_pq_headers_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('book_id')->nullable();  
            $table->string('book_code')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->unsignedBigInteger('sub_store_id')->nullable();
            $table->string('store_code')->nullable();
            $table->string('sub_store_code')->nullable();
            $table->enum('doc_number_type', ConstantHelper::DOC_NO_TYPES) -> default(ConstantHelper::DOC_NO_TYPE_MANUAL);
            $table->enum('doc_reset_pattern', ConstantHelper::DOC_RESET_PATTERNS) -> nullable() -> default(NULL);
            $table->string('doc_prefix') -> nullable();
            $table->string('doc_suffix') -> nullable();
            $table->integer('doc_no') -> nullable();
            $table->string('document_number')->nullable();
            $table->date('document_date')->nullable();
            $table->date('due_date')->nullable();
            $table->string('document_status')->nullable();
            $table->string('revision_number')->nullable();
            $table->date('revision_date')->nullable();
            $table->integer('approval_level')->default(1);
            $table->string('reference_number')->nullable();
            $table->unsignedBigInteger('billing_address')->nullable();
            $table->unsignedBigInteger('shipping_address')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->string('currency_code')->nullable();
            $table->unsignedBigInteger('org_currency_id')->nullable();
            $table->string('org_currency_code')->nullable();
            $table->decimal('org_currency_exg_rate', 15, 6)->nullable();
            $table->unsignedBigInteger('comp_currency_id')->nullable();
            $table->string('comp_currency_code')->nullable();
            $table->decimal('comp_currency_exg_rate', 15, 6)->nullable();
            $table->unsignedBigInteger('group_currency_id')->nullable();
            $table->string('group_currency_code')->nullable();
            $table->decimal('group_currency_exg_rate', 15, 6)->nullable();
            $table->decimal('total_item_count', 15, 2)->nullable();
            $table->string('instructions')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->string('vendor_name')->nullable();
            $table->string('vendor_email')->nullable();
            $table->string('vendor_phone')->nullable();
            $table->string('vendor_gstin')->nullable();
            $table->string('consignee_name')->nullable();
            $table->unsignedBigInteger('payment_terms_id')->nullable();
            $table->string('payment_terms_code')->nullable();
            $table->text('remarks')->nullable();
            $table->decimal('total_quotation_value', 15, 2)->default(0.00);
            $table->decimal('total_tax_value', 15, 2)->default(0.00);
            $table->decimal('total_discount_value', 15, 2)->default(0.00);
            $table->decimal('total_expense_value', 15, 2)->default(0.00);
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            // Example foreign key, adjust as needed
            // $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
        });
        Schema::create('erp_pq_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pq_header_id');
            $table->unsignedBigInteger('rfq_item_id');
            $table->unsignedBigInteger('item_id');
            $table->string('item_code');
            $table->string('item_name')->nullable();
            $table->unsignedBigInteger('uom_id');
            $table->string('uom_code');
            $table->decimal('request_qty', 15, 2)->default(0.00)->comment('Quantity requested in purchase quotation');
            $table->decimal('rate', 15, 2)->default(0.00)->comment(' in purchase quotation');
            $table->decimal('item_discount_amount', 15, 2)->default(0.00)->comment('Discount amount applied at item level');
            $table->decimal('header_discount_amount', 15, 2)->default(0.00)->comment('Discount amount applied at header level for this item');
            $table->decimal('item_expense_amount', 15, 2)->default(0.00)->comment('Expense amount applied at item level');
            $table->decimal('header_expense_amount', 15, 2)->default(0.00)->comment('Expense amount applied at header level for this item');
            $table->decimal('tax_amount', 15, 2)->default(0.00)->comment('Total tax amount for this item');
            $table->decimal('total_item_amount', 15, 2)->default(0.00)->comment('Total amount for this item after discounts, expenses, and taxes');
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('erp_pq_items_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id');
            $table->unsignedBigInteger('pq_header_id');
            $table->unsignedBigInteger('item_id');
            $table->string('item_code');
            $table->string('item_name')->nullable();
            $table->unsignedBigInteger('uom_id');
            $table->string('uom_code');
            $table->decimal('request_qty', 15, 2)->default(0.00)->comment('Quantity requested in purchase quotation');
            $table->decimal('rate', 15, 2)->default(0.00)->comment(' in purchase quotation');
            $table->decimal('item_discount_amount', 15, 2)->default(0.00)->comment('Discount amount applied at item level');
            $table->decimal('header_discount_amount', 15, 2)->default(0.00)->comment('Discount amount applied at header level for this item');
            $table->decimal('item_expense_amount', 15, 2)->default(0.00)->comment('Expense amount applied at item level');
            $table->decimal('header_expense_amount', 15, 2)->default(0.00)->comment('Expense amount applied at header level for this item');
            $table->decimal('tax_amount', 15, 2)->default(0.00)->comment('Total tax amount for this item');
            $table->decimal('total_item_amount', 15, 2)->default(0.00)->comment('Total amount for this item after discounts, expenses, and taxes');
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('erp_pq_item_teds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pq_id')->nullable();
            $table->unsignedBigInteger('pq_item_id')->nullable();
            $table->string('ted_type')->comment('Tax, Expense, Discount');
            $table->string('ted_level')->comment('H or D');
            $table->unsignedBigInteger('ted_id')->nullable();
            $table->string('ted_group_code')->nullable();
            $table->string('ted_name')->nullable();
            $table->decimal('assessment_amount', 15, 2)->default(0.00);
            $table->decimal('ted_percentage', 15, 2)->default(0.00)->comment('TED Percentage');
            $table->decimal('ted_amount', 15, 2)->default(0.00)->comment('TED Amount');
            $table->string('applicable_type')->nullable()->comment('Deduction, Collection');

            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('erp_pq_item_teds_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('pq_id')->nullable();
            $table->unsignedBigInteger('pq_item_id')->nullable();
            $table->string('ted_type')->comment('Tax, Expense, Discount');
            $table->string('ted_level')->comment('H or D');
            $table->unsignedBigInteger('ted_id')->nullable();
            $table->string('ted_group_code')->nullable();
            $table->string('ted_name')->nullable();
            $table->decimal('assessment_amount', 15, 2)->default(0.00);
            $table->decimal('ted_percentage', 15, 2)->default(0.00)->comment('TED Percentage');
            $table->decimal('ted_amount', 15, 2)->default(0.00)->comment('TED Amount');
            $table->string('applicable_type')->nullable()->comment('Deduction, Collection');

            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('erp_pq_item_attributes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pq_id')->nullable();
            $table->unsignedBigInteger('pq_item_id')->nullable();
            $table->unsignedBigInteger('item_attribute_id')->nullable()->comment('Reference to erp_item_attributes');
            $table->string('item_code')->nullable();
            $table->string('attribute_name')->nullable();
            $table->string('attr_name')->nullable();
            $table->string('attribute_value')->nullable();
            $table->string('attr_value')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('erp_pq_item_attributes_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('pq_id')->nullable();
            $table->unsignedBigInteger('pq_item_id')->nullable();
            $table->unsignedBigInteger('item_attribute_id')->nullable()->comment('Reference to erp_item_attributes');
            $table->string('item_code')->nullable();
            $table->string('attribute_name')->nullable();
            $table->string('attr_name')->nullable();
            $table->string('attribute_value')->nullable();
            $table->string('attr_value')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
            Schema::create('erp_pq_media', function (Blueprint $table) {
            $table->id();
            $table->morphs('model');
            $table->uuid()->nullable()->unique();
            $table->string('model_name', 100);
            $table->string('collection_name', 50);
            $table->string('name', 100);
            $table->string('file_name');
            $table->string('mime_type', 50)->nullable();
            $table->string('disk', 100);
            $table->string('conversions_disk', 100)->nullable();
            $table->unsignedBigInteger('size');
            $table->json('manipulations')->nullable();
            $table->json('custom_properties')->nullable();
            $table->json('generated_conversions')->nullable();
            $table->json('responsive_images')->nullable();
            $table->unsignedInteger('order_column')->nullable()->index();
            $table->nullableTimestamps();
        });
        Schema::create('erp_pq_dynamic_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('header_id');
            $table->unsignedBigInteger('dynamic_field_id');
            $table->unsignedBigInteger('dynamic_field_detail_id');
            $table->string('name');
            $table->string('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('erp_pq_dynamic_fields');
        Schema::dropIfExists('erp_pq_media');
        Schema::dropIfExists('erp_pq_item_ted_history');
        Schema::dropIfExists('erp_pq_item_ted');
        Schema::dropIfExists('erp_pq_item_attributes_history');
        Schema::dropIfExists('erp_pq_item_attributes');
        Schema::dropIfExists('erp_pq_items_history');
        Schema::dropIfExists('erp_pq_items');
        Schema::dropIfExists('erp_pq_headers_history');
        Schema::dropIfExists('erp_pq_headers');
    }
};
