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
        # Gate Entry Order
        foreach(['erp_gate_entry_headers', 'erp_gate_entry_headers_history'] as $tbl) {
            Schema::create($tbl, function (Blueprint $table) use ($tbl) {
                $table->id();
                if (str_contains($tbl, 'history')) {
                    $table->unsignedBigInteger('source_id')->nullable();
                }
                $table->unsignedBigInteger('organization_id')->nullable();
                $table->unsignedBigInteger('group_id')->nullable();
                $table->unsignedBigInteger('company_id')->nullable();
                $table->unsignedBigInteger('purchase_order_id')->nullable();
                $table->unsignedBigInteger('series_id')->nullable();
                $table->unsignedBigInteger('book_id')->nullable();
                $table->string('book_code')->nullable();
                $table->enum('doc_number_type', ConstantHelper::DOC_NO_TYPES) -> default(ConstantHelper::DOC_NO_TYPE_MANUAL);
                $table->enum('doc_reset_pattern', ConstantHelper::DOC_RESET_PATTERNS) -> nullable() -> default(NULL);
                $table->string('doc_prefix') -> nullable();
                $table->string('doc_suffix') -> nullable();
                $table->integer('doc_no') -> nullable();
                $table->unsignedBigInteger('vendor_id')->nullable();
                $table->string('vendor_code')->nullable();
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->string('customer_code')->nullable();
                $table->unsignedBigInteger('store_id')->nullable();
                $table->string('document_number')->nullable();
                $table->date('document_date')->nullable();
                $table->string('document_status')->nullable();
                $table->string('revision_number')->nullable();
                $table->date('revision_date')->nullable();
                $table->integer('approval_level')->default(1);
                $table->string('reference_number')->nullable();
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
                $table->string('currency_code')->nullable();
                $table->unsignedBigInteger('payment_term_id')->nullable();
                $table->string('payment_term_code')->nullable();
                $table->string('transaction_currency')->nullable();
                $table->unsignedBigInteger('org_currency_id')->nullable();
                $table->string('org_currency_code')->nullable();
                $table->decimal('org_currency_exg_rate', 15, 6)->nullable();
                $table->unsignedBigInteger('comp_currency_id')->nullable();
                $table->string('comp_currency_code')->nullable();
                $table->decimal('comp_currency_exg_rate', 15, 6)->nullable();
                $table->unsignedBigInteger('group_currency_id')->nullable();
                $table->string('group_currency_code')->nullable();
                $table->decimal('group_currency_exg_rate', 15, 6)->nullable();
                $table->decimal('sub_total', 15, 6)->nullable();
                $table->decimal('total_item_amount', 15, 6)->nullable();
                $table->decimal('item_discount', 15, 6)->nullable();
                $table->decimal('header_discount', 15, 6)->nullable();
                $table->decimal('total_discount', 15, 6)->nullable();
                $table->decimal('gst', 15, 6)->nullable();
                $table->json('gst_details')->nullable();
                $table->decimal('taxable_amount', 15, 6)->nullable();
                $table->decimal('total_taxes', 15, 6)->nullable();
                $table->decimal('total_after_tax_amount', 15, 6)->nullable();
                $table->decimal('expense_amount', 15, 6)->nullable();
                $table->decimal('total_amount', 15, 6)->nullable();
                $table->text('final_remark')->nullable();
                $table->enum('status', ConstantHelper::STATUS)->default(ConstantHelper::ACTIVE)->index();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->unsignedBigInteger('deleted_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        # Gate Entry Item
        foreach(['erp_gate_entry_details', 'erp_gate_entry_details_history'] as $tbl) {
            Schema::create($tbl, function (Blueprint $table) use ($tbl) {
                $table->id();
                if (str_contains($tbl, 'history')) {
                    $table->unsignedBigInteger('source_id')->nullable();
                }
                $table->unsignedBigInteger('header_id')->nullable();
                $table->unsignedBigInteger('item_id')->nullable();
                $table->unsignedBigInteger('purchase_order_item_id')->nullable();
                $table->string('item_code')->nullable();
                $table->string('item_name')->nullable();
                $table->unsignedBigInteger('hsn_id')->nullable();
                $table->string('hsn_code')->nullable();
                $table->unsignedBigInteger('uom_id')->nullable();
                $table->string('uom_code')->nullable();
                $table->unsignedBigInteger('store_id')->nullable();
                $table->string('store_code')->nullable();
                $table->decimal('order_qty', 15, 6)->default(0.00)->nullable();
                $table->decimal('receipt_qty', 15, 6)->default(0.00)->nullable();
                $table->decimal('accepted_qty', 15, 6)->default(0.00)->nullable();
                $table->decimal('rejected_qty', 15, 6)->default(0.00)->nullable();
                $table->decimal('mrn_qty', 15, 6)->default(0.00)->nullable();
                $table->string('inventory_uom')->nullable();
                $table->unsignedBigInteger('inventory_uom_id')->nullable();
                $table->string('inventory_uom_code')->nullable();
                $table->decimal('inventory_uom_qty', 15, 6)->default(0.00)->nullable();
                $table->decimal('rate', 15, 6)->default(0.00)->nullable();
                $table->decimal('basic_value', 15, 6)->default(0.00)->nullable();
                $table->decimal('discount_percentage', 15, 6)->default(0.00)->nullable();
                $table->decimal('discount_amount', 15, 6)->default(0.00)->nullable();
                $table->decimal('header_discount_amount', 15, 6)->default(0.00)->nullable();
                $table->decimal('net_value', 15, 6)->default(0.00)->nullable();
                $table->decimal('tax_value', 15, 6)->default(0.00)->nullable();
                $table->decimal('taxable_amount', 15, 6)->default(0.00)->nullable();
                $table->decimal('item_exp_amount', 15, 6)->default(0.00)->nullable();
                $table->decimal('header_exp_amount', 15, 6)->default(0.00)->nullable();
                $table->decimal('total_item_amount', 15, 6)->default(0.00)->nullable();
                $table->longText('remark')->nullable();
                $table->timestamps();
                $table->softDeletes();

                if (str_contains($tbl, 'history')) {
                    $table->foreign('header_id')->references('id')->on('erp_gate_entry_headers_history')->onDelete('cascade');
                } else {
                    $table->foreign('header_id')->references('id')->on('erp_gate_entry_headers')->onDelete('cascade');
                }
            });
        }

        # Gate Entry Item Attributes
        foreach(['erp_gate_entry_attributes','erp_gate_entry_attributes_history'] as $tbl) {
            Schema::create($tbl, function (Blueprint $table) use ($tbl) {
                $table->id();
                if (str_contains($tbl, 'history')) {
                    $table->unsignedBigInteger('source_id')->nullable();
                }
                $table->unsignedBigInteger('header_id')->nullable();
                $table->unsignedBigInteger('detail_id')->nullable();
                $table->unsignedBigInteger('item_id')->nullable();
                $table->string('item_code')->nullable();
                $table->unsignedBigInteger('item_attribute_id')->nullable()->comment('use tbl erp_item_attributes');
                $table->unsignedBigInteger('attr_name')->nullable();
                $table->unsignedBigInteger('attr_value')->nullable();
                $table->timestamps();
                $table->softDeletes();
                if (str_contains($tbl, 'history')) {
                    $table->foreign('detail_id')->references('id')->on('erp_gate_entry_details_history')->onDelete('cascade');
                    $table->foreign('header_id')->references('id')->on('erp_gate_entry_headers_history')->onDelete('cascade');
                } else {
                    $table->foreign('detail_id')->references('id')->on('erp_gate_entry_details')->onDelete('cascade');
                    $table->foreign('header_id')->references('id')->on('erp_gate_entry_headers')->onDelete('cascade');
                }
            });
        }

        # Gate Entry Item Locations
        foreach(['erp_gate_entry_item_locations','erp_gate_entry_item_locations_history'] as $tbl) {
            Schema::create($tbl, function (Blueprint $table) use ($tbl) {
                $table->id();
                if (str_contains($tbl, 'history')) {
                    $table->unsignedBigInteger('source_id')->nullable();
                }
                $table->unsignedBigInteger('header_id')->nullable();
                $table->unsignedBigInteger('detail_id')->nullable();
                $table->unsignedBigInteger('item_id')->nullable();
                $table->unsignedBigInteger('store_id')->nullable();
                $table->unsignedBigInteger('rack_id')->nullable();
                $table->unsignedBigInteger('shelf_id')->nullable();
                $table->unsignedBigInteger('bin_id')->nullable();
                $table->decimal('quantity', 15,6)->nullable();
                $table->decimal('inventory_uom_qty', 15,6)->nullable();
                $table->timestamps();
                $table->softDeletes();

                if (str_contains($tbl, 'history')) {
                    $table->foreign('detail_id')->references('id')->on('erp_gate_entry_details_history')->onDelete('cascade');
                    $table->foreign('header_id')->references('id')->on('erp_gate_entry_headers_history')->onDelete('cascade');
                } else {
                    $table->foreign('detail_id')->references('id')->on('erp_gate_entry_details')->onDelete('cascade');
                    $table->foreign('header_id')->references('id')->on('erp_gate_entry_headers')->onDelete('cascade');
                }
            });
        }

        # Gate Entry TED
        foreach(['erp_gate_entry_ted','erp_gate_entry_ted_history'] as $tbl) {
            Schema::create($tbl, function (Blueprint $table) use ($tbl) {
                $table->id();
                if (str_contains($tbl, 'history')) {
                    $table->unsignedBigInteger('source_id')->nullable();
                }
                $table->unsignedBigInteger('header_id')->nullable();
                $table->unsignedBigInteger('detail_id')->nullable();
                $table->unsignedBigInteger('ted_id')->nullable();
                $table->string('ted_type',151)->nullable();
                $table->string('ted_level',191)->nullable();
                $table->string('book_code',151)->nullable();
                $table->string('document_number',191)->nullable();
                $table->string('ted_name',151)->nullable();
                $table->string('ted_code',151)->nullable();
                $table->decimal('assesment_amount',10,2)->nullable();
                $table->decimal('ted_percentage',10,2)->nullable();
                $table->decimal('ted_amount',10,2)->nullable();
                $table->string('applicability_type',99)->nullable();
                $table->timestamps();
                $table->softDeletes();

                if (str_contains($tbl, 'history')) {
                    $table->foreign('detail_id')->references('id')->on('erp_gate_entry_details_history')->onDelete('cascade');
                    $table->foreign('header_id')->references('id')->on('erp_gate_entry_headers_history')->onDelete('cascade');
                } else {
                    $table->foreign('detail_id')->references('id')->on('erp_gate_entry_details')->onDelete('cascade');
                    $table->foreign('header_id')->references('id')->on('erp_gate_entry_headers')->onDelete('cascade');
                }
            });
        }

        # Manufactoring Order Media
        Schema::create('erp_gate_entry_media', function (Blueprint $table) use ($tbl) {
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
            $table->json('manipulations');
            $table->json('custom_properties');
            $table->json('generated_conversions');
            $table->json('responsive_images');
            $table->unsignedInteger('order_column')->nullable()->index();
            $table->nullableTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_gate_entry_media');
        Schema::dropIfExists('erp_gate_entry_ted_history');
        Schema::dropIfExists('erp_gate_entry_ted');
        Schema::dropIfExists('erp_gate_entry_item_locations_history');
        Schema::dropIfExists('erp_gate_entry_item_locations');
        Schema::dropIfExists('erp_gate_entry_attributes_history');
        Schema::dropIfExists('erp_gate_entry_attributes');
        Schema::dropIfExists('erp_gate_entry_details_history');
        Schema::dropIfExists('erp_gate_entry_details');
        Schema::dropIfExists('erp_gate_entry_headers_history');
        Schema::dropIfExists('erp_gate_entry_headers');
    }
};
