<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Define the common columns for both tables.
     */
    private function addCommonColumns(Blueprint $table): void
    {
        $table->unsignedBigInteger('organization_id')->nullable();
        $table->unsignedBigInteger('group_id')->nullable();
        $table->unsignedBigInteger('company_id')->nullable();
        $table->unsignedBigInteger('book_id')->nullable();
        $table->boolean('invoice_required')->default(0);
        $table->string('book_code')->nullable();
        $table->string('document_number')->nullable();
        $table->unsignedBigInteger('cost_center_id')->nullable();
        $table->string('doc_number_type')->default('Manually');
        $table->string('doc_reset_pattern')->nullable();
        $table->string('doc_prefix')->nullable();
        $table->string('doc_suffix')->nullable();
        $table->integer('doc_no')->nullable();
        $table->date('document_date')->nullable();
        $table->string('revision_number')->default('0');
        $table->date('revision_date')->nullable();
        $table->string('reference_number')->nullable();
        $table->unsignedBigInteger('store_id')->nullable();
        $table->unsignedBigInteger('sub_store_id')->nullable();
        $table->string('store_code')->nullable();
        $table->unsignedBigInteger('department_id')->nullable();
        $table->string('department_code')->nullable();
        $table->unsignedBigInteger('customer_id')->nullable();
        $table->string('customer_code')->nullable();
        $table->string('customer_email')->nullable();
        $table->string('customer_phone_no', 20)->nullable();
        $table->string('customer_gstin', 20)->nullable();
        $table->string('consignee_name')->nullable();
        $table->string('consignment_no')->nullable();
        $table->string('eway_bill_no')->nullable();
        $table->unsignedBigInteger('eway_bill_master_id')->nullable();
        $table->string('transportation_mode', 191)->nullable();
        $table->string('transporter_name')->nullable();
        $table->string('vehicle_no')->nullable();
        $table->string('lr_number', 25)->nullable();
        $table->unsignedBigInteger('billing_address')->nullable();
        $table->unsignedBigInteger('shipping_address')->nullable();
        $table->unsignedBigInteger('currency_id')->nullable();
        $table->string('currency_code')->nullable();
        $table->unsignedBigInteger('payment_term_id')->nullable();
        $table->string('payment_term_code')->nullable();
        $table->string('document_status')->nullable();
        $table->tinyInteger('delivery_status')->default(0);
        $table->string('document_type')->nullable();
        $table->string('gst_invoice_type', 10)->default('B2B');
        $table->string('e_invoice_status', 25)->nullable();
        $table->tinyInteger('is_ewb_generated')->default(0);
        $table->integer('approval_level')->default(1)->comment('current approval level');
        $table->text('remarks')->nullable();
        $table->decimal('total_item_value', 15, 2)->default(0.00);
        $table->decimal('total_discount_value', 15, 2)->default(0.00);
        $table->decimal('total_tax_value', 15, 2)->default(0.00);
        $table->decimal('total_expense_value', 15, 2)->default(0.00);
        $table->decimal('total_amount', 15, 2)->default(0.00);
        $table->text('book_terms')->nullable();
        $table->unsignedBigInteger('book_terms_id')->nullable();
        $table->text('customer_terms')->nullable();
        $table->unsignedBigInteger('customer_terms_id')->nullable();
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
    }

    public function up(): void
    {
        Schema::create('erp_transport_invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $this->addCommonColumns($table);
        });

        Schema::create('erp_transport_invoices_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('source_id'); // Links back to main table
            $this->addCommonColumns($table);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_transport_invoices_history');
        Schema::dropIfExists('erp_transport_invoices');
    }
};
