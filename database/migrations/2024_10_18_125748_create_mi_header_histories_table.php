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
        Schema::create('erp_mi_header_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('book_id')->nullable(); 
            $table->string('book_code')->nullable();
            $table->unsignedBigInteger('header_id')->nullable();
            $table->unsignedBigInteger('mr_header_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->string('store_code')->nullable();
            $table->string('document_number')->nullable();
            $table->date('document_date')->nullable();
            $table->string('document_status')->nullable();
            $table->string('revision_number')->nullable();
            $table->date('revision_date')->nullable();
            $table->integer('approval_level')->default(1);
            $table->string('reference_number')->nullable();
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
            $table->decimal('total_item_amount', 15, 2)->nullable();
            $table->decimal('item_discount', 15, 2)->nullable();
            $table->decimal('header_discount', 15, 2)->nullable();
            $table->decimal('total_discount', 15, 2)->nullable();
            $table->decimal('taxable_amount', 15, 2)->nullable();
            $table->decimal('total_taxes', 15, 2)->nullable();
            $table->decimal('total_after_tax_amount', 15, 2)->nullable();
            $table->decimal('expense_amount', 15, 2)->nullable();
            $table->decimal('total_amount', 15, 2)->nullable(); 
            $table->text('remark')->nullable();
            $table->enum('status', ConstantHelper::STATUS)->default(ConstantHelper::ACTIVE)->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('erp_mi_detail_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('header_id')->nullable();
            $table->unsignedBigInteger('header_history_id')->nullable();
            $table->unsignedBigInteger('detail_id')->nullable();
            $table->unsignedBigInteger('mr_detail_id')->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('item_code')->nullable();
            $table->string('item_name')->nullable();
            $table->unsignedBigInteger('hsn_id')->nullable();
            $table->string('hsn_code')->nullable();
            $table->unsignedBigInteger('uom_id')->nullable();
            $table->string('uom_code')->nullable();
            $table->decimal('quantity', 15, 2)->default(0.00)->nullable(); 
            $table->string('inventory_uom')->nullable();
            $table->unsignedBigInteger('inventory_uom_id')->nullable();
            $table->string('inventory_uom_code')->nullable();
            $table->decimal('inventory_uom_qty', 15, 2)->default(0.00)->nullable();
            $table->decimal('rate', 15, 2)->default(0.00)->nullable(); 
            $table->decimal('basic_value', 15, 2)->default(0.00)->nullable();
            $table->decimal('discount_amount', 15, 2)->default(0.00)->nullable(); 
            $table->decimal('header_discount_amount', 15, 2)->default(0.00)->nullable(); 
            $table->decimal('net_value', 15, 2)->default(0.00)->nullable();
            $table->decimal('sgst_percentage', 15, 2)->default(0.00)->nullable(); 
            $table->decimal('cgst_percentage', 15, 2)->default(0.00)->nullable(); 
            $table->decimal('igst_percentage', 15, 2)->default(0.00)->nullable(); 
            $table->decimal('tax_value', 15, 2)->default(0.00)->nullable(); 
            $table->decimal('item_exp_amount', 15, 2)->default(0.00)->nullable();
            $table->decimal('header_exp_amount', 15, 2)->default(0.00)->nullable();
            $table->longText('remark')->nullable();
            $table->timestamps(); 
            $table->softDeletes();
        });

        Schema::create('erp_mi_item_attribute_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('header_id')->nullable();
            $table->unsignedBigInteger('header_history_id')->nullable();
            $table->unsignedBigInteger('detail_id')->nullable();
            $table->unsignedBigInteger('detail_history_id')->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('item_code')->nullable();
            $table->unsignedBigInteger('attribute_id')->nullable();
            $table->unsignedBigInteger('item_attribute_id')->nullable();

            $table->string('attr_name')->nullable();
            $table->string('attr_value')->nullable();
            $table->timestamps(); 
            $table->softDeletes();
        });

        Schema::create('erp_mi_ted_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('header_id')->nullable();
            $table->unsignedBigInteger('header_history_id')->nullable();
            $table->unsignedBigInteger('detail_id')->nullable();
            $table->unsignedBigInteger('detail_history_id')->nullable();
            $table->unsignedBigInteger('mr_ted_id')->nullable();
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
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_mi_ted_histories');
        Schema::dropIfExists('erp_mi_item_attribute_histories');
        Schema::dropIfExists('erp_mi_detail_histories');
        Schema::dropIfExists('erp_mi_header_histories');
    }
};
