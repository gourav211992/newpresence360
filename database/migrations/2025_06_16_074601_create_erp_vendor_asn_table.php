<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('erp_vendor_asn', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id');
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('type')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->unsignedBigInteger('book_id')->nullable();
            $table->string('book_code')->nullable();
            $table->string('document_number')->nullable();
            $table->enum('doc_number_type', ['Auto', 'Manually'])->default('Manually');
            $table->enum('doc_reset_pattern', ['Never', 'Yearly', 'Quarterly', 'Monthly'])->nullable();
            $table->string('doc_prefix')->nullable();
            $table->string('doc_suffix')->nullable();
            $table->integer('doc_no')->nullable();
            $table->date('document_date')->nullable();
            $table->string('eway_bill_no', 250)->nullable();
            $table->string('consignment_no', 250)->nullable();
            $table->string('suppl_invoice_no', 250)->nullable();
            $table->date('suppl_invoice_date')->nullable();
            $table->string('transporter_name', 500)->nullable();
            $table->string('vehicle_no', 250)->nullable();
            $table->string('revision_number')->default('0');
            $table->date('revision_date')->nullable();
            $table->string('reference_number')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->string('vendor_code')->nullable();
            $table->unsignedBigInteger('billing_address')->nullable();
            $table->unsignedBigInteger('shipping_address')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->string('currency_code')->nullable();
            $table->enum('document_status', ['draft', 'submitted'])->nullable();
            $table->integer('approval_level')->default(1)->comment('current approval level');
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('payment_term_id')->nullable();
            $table->string('payment_term_code')->nullable();
            $table->decimal('total_item_value', 15, 2)->default(0.00);
            $table->decimal('total_discount_value', 15, 2)->default(0.00);
            $table->decimal('total_tax_value', 15, 2)->default(0.00);
            $table->decimal('total_expense_value', 15, 2)->default(0.00);
            $table->unsignedBigInteger('org_currency_id')->nullable();
            $table->string('org_currency_code')->nullable();
            $table->decimal('org_currency_exg_rate', 15, 6)->nullable();
            $table->unsignedBigInteger('comp_currency_id')->nullable();
            $table->string('comp_currency_code')->nullable();
            $table->decimal('comp_currency_exg_rate', 15, 6)->nullable();
            $table->unsignedBigInteger('group_currency_id')->nullable();
            $table->string('group_currency_code')->nullable();
            $table->decimal('group_currency_exg_rate', 15, 6)->nullable();
            $table->enum('gate_entry_required', ['yes', 'no'])->nullable();
            $table->enum('supp_invoice_required', ['yes', 'no'])->nullable();
            $table->enum('partial_delivery', ['yes', 'no'])->nullable();
            $table->text('invoice_file_path')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('erp_vendor_asn_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_asn_id');
            $table->unsignedBigInteger('po_item_id');
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('item_code')->nullable();
            $table->string('item_name')->nullable();
            $table->unsignedBigInteger('hsn_id')->nullable();
            $table->string('hsn_code')->nullable();
            $table->unsignedBigInteger('uom_id')->nullable();
            $table->string('uom_code')->nullable();
            $table->decimal('order_qty', 15, 2)->default(0.00);
            $table->decimal('supplied_qty', 15, 2)->default(0.00);
            $table->decimal('balance_qty', 15, 2)->default(0.00);
            $table->decimal('grn_qty', 15, 2)->default(0.00);
            $table->decimal('expense_advise_qty', 15, 2)->default(0.00);
            $table->double('invoice_quantity')->default(0);
            $table->double('short_close_qty', 15, 2)->default(0.00);
            $table->double('ge_qty', 20, 6)->default(0.00);
            $table->unsignedBigInteger('so_id')->nullable();
            $table->unsignedBigInteger('inventory_uom_id')->nullable();
            $table->string('inventory_uom_code')->nullable();
            $table->decimal('inventory_uom_qty', 15, 2)->default(0.00);
            $table->decimal('rate', 15, 2)->default(0.00);
            $table->decimal('item_discount_amount', 15, 2)->default(0.00);
            $table->decimal('header_discount_amount', 15, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('expense_amount', 15, 2)->default(0.00);
            $table->unsignedBigInteger('company_currency_id')->nullable();
            $table->unsignedBigInteger('company_currency_exchange_rate')->nullable();
            $table->unsignedBigInteger('group_currency_id')->nullable();
            $table->unsignedBigInteger('group_currency_exchange_rate')->nullable();
            $table->text('remarks')->nullable();
            $table->date('delivery_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_vendor_asn_items');
        Schema::dropIfExists('erp_vendor_asn');
    }
};
