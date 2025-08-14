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
        Schema::create('erp_sale_invoices_history', function (Blueprint $table) {
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
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_code')->nullable();
            $table->string('consignee_name')->nullable();
            $table->string('consignment_no')->nullable();
            $table->string('eway_bill_no')->nullable();
            $table->string('transporter_name')->nullable();
            $table->string('vehicle_no')->nullable();

            $table->unsignedBigInteger('billing_address')->nullable();
            $table->unsignedBigInteger('shipping_address')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->string('currency_code')->nullable();
            $table->unsignedBigInteger('payment_term_id')->nullable();
            $table->string('payment_term_code')->nullable();
            $table->string('document_status')->nullable();
            $table->enum('document_type', ConstantHelper::SALE_INVOICE_DOC_TYPES) -> default(ConstantHelper::SI_SERVICE_ALIAS);
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
            //$table->foreign('created_by')->references('id')->on('users')->onDelete('NO ACTION');
            $table->unsignedBigInteger('updated_by')->nullable();
            //$table->foreign('updated_by')->references('id')->on('users')->onDelete('NO ACTION');
            $table->unsignedBigInteger('deleted_by')->nullable();
            //$table->foreign('deleted_by')->references('id')->on('users')->onDelete('NO ACTION');
            
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('erp_invoice_items_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('sale_invoice_id')->nullable();
            $table->unsignedBigInteger('so_item_id')->nullable()->comment('erp_so_item_id');
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('item_code')->nullable();
            $table->string('item_name')->nullable();
            $table->unsignedBigInteger('hsn_id')->nullable();
            $table->string('hsn_code')->nullable();
            $table->unsignedBigInteger('uom_id')->nullable();
            $table->string('uom_code')->nullable();
            $table->decimal('order_qty',15, 2)->default(0.00);
            $table->unsignedBigInteger('sale_order_id')->nullable();
            $table->unsignedBigInteger('dn_id')->nullable()->comment('From which header this has been pulled');
            $table->unsignedBigInteger('invoice_id')->nullable()->comment('From which header this has been pulled');
            $table->decimal('invoice_qty',15, 2)->default(0.00);
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
            $table->foreign('sale_invoice_id')->references('id')->on('erp_sale_invoices_history')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('erp_invoice_item_attributes_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('sale_invoice_id')->nullable();
            $table->unsignedBigInteger('invoice_item_id')->nullable();
            $table->unsignedBigInteger('item_attribute_id')->nullable()->comment('use tbl erp_item_attributes');
            $table->string('item_code')->nullable();
            $table->string('attribute_name')->nullable();
            $table->unsignedBigInteger('attr_name')->nullable();
            $table->string('attribute_value')->nullable();
            $table->unsignedBigInteger('attr_value')->nullable();

            $table->foreign('invoice_item_id')->references('id')->on('erp_invoice_items_history')->onDelete('cascade');
            $table->foreign('sale_invoice_id')->references('id')->on('erp_sale_invoices_history')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('erp_invoice_item_locations_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('sale_invoice_id')->nullable();
            $table->unsignedBigInteger('invoice_item_id')->nullable();
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
            $table->double('quantity', 15, 2)->default(0.00);
            $table->double('inventory_uom_qty', 15, 2)->default(0.00);
            $table->foreign('invoice_item_id')->references('id')->on('erp_invoice_items_history')->onDelete('cascade');
            $table->foreign('sale_invoice_id')->references('id')->on('erp_sale_invoices_history')->onDelete('cascade');

            $table->foreign('store_id')->references('id')->on('erp_stores')->onDelete('cascade');
            $table->foreign('rack_id')->references('id')->on('erp_racks')->onDelete('cascade');
            $table->foreign('shelf_id')->references('id')->on('erp_shelfs')->onDelete('cascade');
            $table->foreign('bin_id')->references('id')->on('erp_bins')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('erp_sale_invoice_ted_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('sale_invoice_id')->nullable();
            $table->unsignedBigInteger('invoice_item_id')->nullable();
            $table->enum('ted_type', ['Tax', 'Expense', 'Discount'])->comment('Tax, Expense, Discount');
            $table->enum('ted_level', ['H', 'D'])->comment('H or D');
            $table->unsignedBigInteger('ted_id')->nullable();
            $table->string('ted_group_code')->nullable();
            $table->string('ted_name')->nullable();
            $table->decimal('assessment_amount',15, 2)->default(0.00);
            $table->decimal('ted_percentage',15, 2)->default(0.00)->comment('TED Percentage');
            $table->decimal('ted_amount',15, 2)->default(0.00)->comment('TED Amount');
            $table->string('applicable_type')->nullable()->comment('Deduction, Collection');
            $table->foreign('sale_invoice_id')->references('id')->on('erp_sale_invoices_history')->onDelete('cascade');
            $table->foreign('invoice_item_id')->references('id')->on('erp_invoice_items_history')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_sale_invoices_history');
        Schema::dropIfExists('erp_invoice_items_history');
        Schema::dropIfExists('erp_invoice_item_attributes_history');
        Schema::dropIfExists('erp_invoice_item_locations_history');
        Schema::dropIfExists('erp_sale_invoice_ted_history');
    }
};
