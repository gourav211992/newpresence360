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
        Schema::dropIfExists('erp_purchase_order_ted_history');
        Schema::dropIfExists('erp_po_terms_history');
        Schema::dropIfExists('erp_po_item_delivery_history');
        Schema::dropIfExists('erp_po_item_attributes_history');
        Schema::dropIfExists('erp_po_items_history');
        Schema::dropIfExists('erp_purchase_orders_history');
        # po history
        Schema::create('erp_purchase_orders_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('book_id')->nullable(); 
            $table->string('book_code')->nullable(); 
            $table->string('document_number')->nullable(); 
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

            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            // $table->foreign('created_by')->references('id')->on('users')->onDelete('NO ACTION');
            $table->unsignedBigInteger('updated_by')->nullable();
            // $table->foreign('updated_by')->references('id')->on('users')->onDelete('NO ACTION');
            $table->unsignedBigInteger('deleted_by')->nullable();
            // $table->foreign('deleted_by')->references('id')->on('users')->onDelete('NO ACTION');
        });

        # po item history
        Schema::create('erp_po_items_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('item_code')->nullable();
            $table->string('item_name')->nullable();
            $table->unsignedBigInteger('hsn_id')->nullable();
            $table->string('hsn_code')->nullable();
            $table->unsignedBigInteger('uom_id')->nullable();
            $table->string('uom_code')->nullable();
            $table->decimal('order_qty',15, 2)->default(0.00);
            $table->decimal('grn_qty',15, 2)->default(0.00);
            $table->unsignedBigInteger('inventory_uom_id')->nullable();
            $table->string('inventory_uom_code')->nullable();
            $table->decimal('inventory_uom_qty',15, 2)->default(0.00);
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
            $table->foreign('purchase_order_id')->references('id')->on('erp_purchase_orders')->onDelete('cascade');
            $table->timestamps(); 
            $table->softDeletes();
        });

        # po item attr history
        Schema::create('erp_po_item_attributes_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('po_item_id')->nullable();
            $table->unsignedBigInteger('item_attribute_id')->nullable()->comment('use tbl erp_item_attributes');
            $table->string('item_code')->nullable();
            $table->string('attribute_name')->nullable();
            $table->string('attribute_value')->nullable();

            $table->foreign('po_item_id')->references('id')->on('erp_po_items')->onDelete('cascade');
            $table->foreign('purchase_order_id')->references('id')->on('erp_purchase_orders')->onDelete('cascade');

            $table->timestamps(); 
            $table->softDeletes();
        });

        # po item delivery history
        Schema::create('erp_po_item_delivery_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('po_item_id')->nullable();
            $table->decimal('qty',15, 2)->default(0.00);
            $table->decimal('grn_qty',15, 2)->default(0.00);
            $table->date('delivery_date');
            $table->foreign('po_item_id')->references('id')->on('erp_po_items')->onDelete('cascade');
            $table->foreign('purchase_order_id')->references('id')->on('erp_purchase_orders')->onDelete('cascade');

            $table->timestamps(); 
            $table->softDeletes();
        });

        # po term history
        Schema::create('erp_po_terms_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('term_id')->nullable();
            $table->string('term_code')->nullable();
            $table->text('remarks')->nullable();
            $table->foreign('purchase_order_id')->references('id')->on('erp_purchase_orders')->onDelete('cascade');

            $table->timestamps(); 
            $table->softDeletes();
        });

        # po ted history 
        Schema::create('erp_purchase_order_ted_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('po_item_id')->nullable();
            $table->unsignedBigInteger('ledger_id')->nullable();
            $table->string('ted_type')->comment('Tax, Expense, Discount');
            $table->string('ted_level')->comment('H or D');
            $table->unsignedBigInteger('ted_id')->nullable();
            $table->string('ted_name')->nullable();
            $table->decimal('assessment_amount',15, 2)->default(0.00);
            $table->decimal('ted_perc',15, 2)->default(0.00)->comment('TED Percentage');
            $table->decimal('ted_amount',15, 2)->default(0.00)->comment('TED Amount');
            $table->string('applicable_type')->nullable()->comment('Deduction, Collection');

            $table->foreign('ledger_id')->references('id')->on('erp_ledgers')->onDelete('cascade');
            $table->foreign('purchase_order_id')->references('id')->on('erp_purchase_orders')->onDelete('cascade');
            $table->foreign('po_item_id')->references('id')->on('erp_po_items')->onDelete('cascade');
            $table->timestamps(); 
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_purchase_orders_history');
        Schema::dropIfExists('erp_po_items_history');
        Schema::dropIfExists('erp_po_item_attributes_history');
        Schema::dropIfExists('erp_po_item_delivery_history');
        Schema::dropIfExists('erp_po_terms_history');
        Schema::dropIfExists('erp_purchase_order_ted_history');
    }
};
