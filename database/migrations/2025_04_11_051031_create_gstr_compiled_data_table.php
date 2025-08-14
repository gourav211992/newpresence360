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
        Schema::create('erp_gstr_compiled_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('voucher_id')->nullable()->index();
            $table->unsignedBigInteger('invoice_id')->nullable()->index();
            $table->string('invoice_no', 50)->nullable()->index();
            $table->date('invoice_date')->nullable();
            $table->string('Revised_invoice_no', 50)->nullable()->index();
            $table->date('Revised_invoice_date')->nullable();
            $table->string('supply_type', 250)->nullable();
            $table->string('invoice_type', 250)->nullable();
            $table->unsignedBigInteger('invoice_type_id')->nullable()->index();
            $table->unsignedBigInteger('group_id')->nullable()->index();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('organization_id')->nullable()->index();
            $table->string('party_name',100)->nullable()->index();
            $table->string('party_gstin',20)->nullable()->index();
            $table->string('voucher_type',30)->nullable()->index();
            $table->string('voucher_no',30)->nullable()->index();
            $table->double('taxable_amt')->default(0);
            $table->integer('pos')->nullable();
            $table->string('place_of_supply',100)->nullable();
            $table->string('reverse_charge',5)->nullable();
            $table->string('hsn_code',100)->nullable()->index();
            $table->string('uqc',100)->nullable()->index();
            $table->string('e_commerce_gstin', 250)->nullable();
            $table->string('revised_ecom_gstin', 250)->nullable();
            $table->string('ecom_operator_name', 200)->nullable();
            $table->double('rate')->default(0);
            $table->double('sgst')->default(0);
            $table->double('cgst')->default(0);
            $table->double('igst')->default(0);
            $table->double('cess')->default(0);
            $table->double('invoice_amt')->default(0);
            $table->double('applicable_tax_rate')->default(0);
            $table->boolean('is_conflict')->default(0)->index();
            $table->text('conflict_msg')->nullable();
            $table->date('note_date')->nullable();
            $table->string('note_type',250)->nullable();
            $table->double('note_value')->default(0);
            $table->string('note_number',250)->nullable();
            $table->string('revised_note_no',250)->nullable();
            $table->date('revised_note_date')->nullable();
            $table->string('ur_type',100)->nullable();
            $table->string('exp_type',100)->nullable();
            $table->string('port_code',100)->nullable();
            $table->string('shipping_bill_no',100)->nullable();
            $table->date('shipping_bill_date')->nullable();
            $table->string('description',100)->nullable();
            $table->double('expt_amt')->default(0);
            $table->double('non_gst_amt')->default(0);
            $table->double('nil_amt')->default(0);
            $table->integer('qty')->nullable();
            $table->string('nature_of_document',100)->nullable();
            $table->string('sr_no_from')->nullable();
            $table->string('sr_no_to')->nullable();
            $table->string('total_number',100)->nullable();
            $table->string('cancelled',100)->nullable();
            $table->double('net_value_of_supplies')->default(0);
            $table->string('supplier_gstin',100)->nullable();
            $table->string('supplier_name',100)->nullable();
            $table->string('doc_no',100)->nullable();
            $table->date('doc_date')->nullable();
            $table->string('revised_doc_no',100)->nullable();
            $table->date('revised_doc_date')->nullable();
            $table->string('doc_type',100)->nullable();
            $table->double('value_of_supplies_made')->default(0);
            $table->integer('year')->nullable();
            $table->integer('month')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_gstr_compiled_data');
    }
};
