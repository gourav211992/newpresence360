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
        Schema::create('erp_mrn_header_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mrn_header_id')->nullable(); 
            $table->unsignedBigInteger('series_id')->nullable(); 
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->unsignedBigInteger('purchase_order_id')->nullable();
            $table->string('mrn_code')->nullable();
            $table->string('mrn_no')->nullable();
            $table->date('mrn_date')->nullable();
            $table->string('document_number')->nullable();
            $table->date('document_date')->nullable();
            $table->string('document_status')->nullable();
            $table->string('revision_number')->nullable();
            $table->date('revision_date')->nullable();
            $table->integer('approval_level')->default(1);
            $table->string('reference_number')->nullable();
            $table->string('mrn_type')->nullable();
            $table->string('gate_entry_no')->nullable();
            $table->date('gate_entry_date')->nullable();
            $table->string('supplier_invoice_no')->nullable();
            $table->date('supplier_invoice_date')->nullable();
            $table->string('eway_bill_no')->nullable();
            $table->string('consignment_no')->nullable();
            $table->string('transporter_name')->nullable();
            $table->string('vehicle_no')->nullable();
            $table->string('billing_to')->nullable();
            $table->string('ship_to')->nullable();
            $table->json('billing_address')->nullable(); 
            $table->json('shipping_address')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable(); 
            $table->string('transaction_currency')->nullable();
            $table->decimal('sub_total', 15, 2)->nullable();
            $table->decimal('total_item_amount', 15, 2)->nullable();
            $table->decimal('discount', 15, 2)->nullable();
            $table->decimal('discount_amount', 15, 2)->nullable();
            $table->decimal('gst', 15, 2)->nullable();
            $table->json('gst_details')->nullable();
            $table->decimal('taxable_amount', 15, 2)->nullable();
            $table->decimal('expense_amount', 15, 2)->nullable();
            $table->decimal('total_amount', 15, 2)->nullable(); 
            $table->text('item_remark')->nullable();
            $table->text('final_remarks')->nullable();
            $table->json('attachment')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('erp_mrn_detail_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mrn_header_history_id')->nullable();
            $table->unsignedBigInteger('mrn_header_id')->nullable();
            $table->unsignedBigInteger('purchase_order_item_id')->nullable();
            $table->unsignedBigInteger('mrn_detail_id')->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('item_code')->nullable();
            $table->string('item_name')->nullable();
            $table->string('hsn_code')->nullable();
            $table->string('store_location')->nullable();
            $table->string('rack')->nullable();
            $table->string('shelf')->nullable();
            $table->string('bin')->nullable();
            $table->unsignedBigInteger('uom_id')->nullable();
            $table->decimal('order_qty', 10, 2)->nullable();
            $table->decimal('receipt_qty', 10, 2)->nullable();
            $table->decimal('accepted_qty', 10, 2)->nullable(); 
            $table->decimal('rejected_qty', 10, 2)->nullable();
            $table->string('inventory_uom')->nullable();
            $table->unsignedBigInteger('inventory_uom_id')->nullable();
            $table->decimal('order_qty_inventory_uom', 10, 2)->nullable();
            $table->decimal('receipt_qty_inventory_uom', 10, 2)->nullable();
            $table->decimal('accepted_qty_inventory_uom', 10, 2)->nullable(); 
            $table->decimal('rejected_qty_inventory_uom', 10, 2)->nullable();
            $table->decimal('rate', 10, 2)->nullable(); 
            $table->decimal('basic_value', 10, 2)->nullable();
            $table->decimal('discount_percentage', 5, 2)->nullable(); 
            $table->decimal('discount_amount', 10, 2)->nullable(); 
            $table->decimal('net_value', 10, 2)->nullable();
            $table->decimal('sgst_percentage', 5, 2)->nullable(); 
            $table->decimal('cgst_percentage', 5, 2)->nullable(); 
            $table->decimal('igst_percentage', 5, 2)->nullable(); 
            $table->decimal('tax_value', 5, 2)->nullable(); 
            $table->decimal('taxable_amount', 5, 2)->nullable(); 
            $table->decimal('sub_total', 15, 2)->nullable();
            $table->decimal('item_exp_amount', 15, 2)->nullable();
            $table->decimal('header_exp_amount', 10, 2)->nullable();
            $table->string('company_currency')->nullable();
            $table->string('exchange_rate_to_company_currency')->nullable();
            $table->string('group_currency')->nullable();
            $table->string('exchange_rate_to_group_currency')->nullable();
            $table->boolean('selected_item')->default(false);
            $table->longText('remark')->nullable();
            $table->timestamps(); 
            $table->softDeletes();
        });

        Schema::create('erp_mrn_attribute_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mrn_header_history_id')->nullable();
            $table->unsignedBigInteger('mrn_detail_history_id')->nullable();
            $table->unsignedBigInteger('mrn_header_id')->nullable();
            $table->unsignedBigInteger('mrn_detail_id')->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->unsignedBigInteger('item_attribute_id')->nullable();
            $table->unsignedBigInteger('mrn_attribute_id')->nullable();

            $table->string('attr_name')->nullable();
            $table->string('attr_value')->nullable();
            $table->timestamps(); 
            $table->softDeletes();

        });

        Schema::create('erp_mrn_extra_amount_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mrn_header_history_id')->nullable();
            $table->unsignedBigInteger('mrn_detail_history_id')->nullable();
            $table->unsignedBigInteger('mrn_header_id')->nullable();
            $table->unsignedBigInteger('mrn_detail_id')->nullable();
            $table->unsignedBigInteger('mrn_extra_amount_id')->nullable();
            $table->string('ted_type',151)->nullable();
            $table->string('ted_level',191)->nullable();
            $table->string('book_code',151)->nullable();
            $table->string('document_number',191)->nullable();
            $table->string('ted_code',151)->nullable();
            $table->decimal('assesment_amount',10,2)->nullable();
            $table->decimal('ted_percentage',10,2)->nullable();
            $table->decimal('ted_amount',10,2)->nullable();
            $table->string('applicability_type',99)->nullable();
            $table->timestamps(); 
            $table->softDeletes();
        });

        Schema::create('erp_mrn_item_location_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mrn_header_history_id')->nullable();
            $table->unsignedBigInteger('mrn_detail_history_id')->nullable();
            $table->unsignedBigInteger('mrn_header_id')->nullable();
            $table->unsignedBigInteger('mrn_detail_id')->nullable();
            $table->unsignedBigInteger('mrn_item_location_id')->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->unsignedBigInteger('rack_id')->nullable();
            $table->unsignedBigInteger('shelf_id')->nullable();
            $table->unsignedBigInteger('bin_id')->nullable();
            $table->decimal('quantity', 10,2)->nullable();
            $table->timestamps(); 
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_mrn_item_location_histories');
        Schema::dropIfExists('erp_mrn_extra_amount_histories');
        Schema::dropIfExists('erp_mrn_attribute_histories');
        Schema::dropIfExists('erp_mrn_detail_histories');
        Schema::dropIfExists('erp_mrn_header_histories');
    }
};
