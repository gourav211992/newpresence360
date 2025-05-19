<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Helpers\ConstantHelper;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('erp_sale_returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('book_id');
            $table->string('book_code');
            $table->string('document_number');
            $table->enum('document_type', ['sr', 'lease-return','dnote','sretdnote'])->default('sr');

            $table->enum('doc_number_type', ['Auto', 'Manually'])->default('Manually');
            $table->enum('doc_reset_pattern', ['Never', 'Yearly', 'Quarterly', 'Monthly'])->nullable();
            $table->string('doc_prefix')->nullable();
            $table->string('doc_suffix')->nullable();
            $table->integer('doc_no')->nullable();
            $table->date('document_date');
            $table->string('revision_number')->default(0);
            $table->string('reference_number')->nullable();
            $table->unsignedBigInteger('customer_id');
            $table->string('customer_code');
            $table->string('consignee_name')->nullable();
            $table->string('consignment_no')->nullable();
            $table->string('eway_bill_no')->nullable();
            $table->string('transporter_name')->nullable();
            $table->string('vehicle_no')->nullable();
            $table->unsignedBigInteger('billing_address')->nullable();
            $table->unsignedBigInteger('shipping_address')->nullable();
            $table->unsignedBigInteger('currency_id');
            $table->string('currency_code');
            $table->unsignedBigInteger('payment_term_id')->nullable();
            $table->string('payment_term_code')->nullable();
            $table->string('document_status')->nullable();
            $table->integer('approval_level')->default(1)->comment('current approval level');
            $table->text('remarks')->nullable(); 
            $table->unsignedBigInteger('org_currency_id')->nullable();
            $table->string('org_currency_code')->nullable();
            $table->decimal('org_currency_exg_rate', 15, 6)->nullable();
            $table->unsignedBigInteger('comp_currency_id')->nullable();
            $table->string('comp_currency_code')->nullable();
            $table->decimal('comp_currency_exg_rate', 15, 6)->nullable();
            $table->unsignedBigInteger('group_currency_id')->nullable();
            $table->string('group_currency_code')->nullable();
            $table->decimal('group_currency_exg_rate', 15, 6)->nullable();
            $table->decimal('total_return_value', 15, 2)->default(0.00);
            $table->decimal('total_tax_value', 15, 2)->default(0.00);
            $table->decimal('total_discount_value', 15, 2)->default(0.00);
            $table->decimal('total_expense_value', 15, 2)->default(0.00);
            $table->decimal('total_amount', 15, 2)->default(0.00);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_sale_returns');
    }
};
