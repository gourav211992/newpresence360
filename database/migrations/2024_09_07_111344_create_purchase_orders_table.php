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
        Schema::dropIfExists('erp_purchase_order_items');
        Schema::dropIfExists('erp_purchase_orders');

        Schema::create('erp_purchase_orders', function (Blueprint $table) {
            $table->id();
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
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('erp_po_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id')->nullable();
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

        Schema::create('erp_po_item_attributes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id')->nullable();
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

        Schema::create('erp_po_item_delivery', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id')->nullable();
            $table->unsignedBigInteger('po_item_id')->nullable();
            $table->decimal('qty',15, 2)->default(0.00);
            $table->decimal('grn_qty',15, 2)->default(0.00);
            $table->date('delivery_date');
            $table->foreign('po_item_id')->references('id')->on('erp_po_items')->onDelete('cascade');
            $table->foreign('purchase_order_id')->references('id')->on('erp_purchase_orders')->onDelete('cascade');

            $table->timestamps(); 
            $table->softDeletes();
        });

        Schema::create('erp_po_terms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id')->nullable();
            $table->unsignedBigInteger('term_id')->nullable();
            $table->string('term_code')->nullable();
            $table->text('remarks')->nullable();
            $table->foreign('purchase_order_id')->references('id')->on('erp_purchase_orders')->onDelete('cascade');

            $table->timestamps(); 
            $table->softDeletes();
        });

        Schema::create('erp_purchase_order_ted', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id')->nullable();
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
        Schema::dropIfExists('erp_purchase_order_ted');
        Schema::dropIfExists('erp_po_terms');
        Schema::dropIfExists('erp_po_item_delivery');
        Schema::dropIfExists('erp_po_item_attributes');
        Schema::dropIfExists('erp_po_items');
        Schema::dropIfExists('erp_purchase_orders');
    }
};
