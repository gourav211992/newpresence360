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
        Schema::create('erp_sale_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_order_id')->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('hsn_code')->nullable();
            $table->unsignedBigInteger('transaction_uom_id')->nullable();
            $table->unsignedBigInteger('inventory_uom_id')->nullable();
            $table->decimal('quantity', 10, 2)->nullable();
            $table->decimal('inventory_quantity', 10, 2)->nullable();
            $table->decimal('invoiced_quantity', 10, 2)->nullable();
            $table->decimal('rate', 10, 2)->nullable(); 
            $table->decimal('basic_value', 10, 2)->nullable();
            $table->decimal('discount_percentage', 5, 2)->nullable(); 
            $table->decimal('discount_amount', 10, 2)->nullable(); 
            $table->decimal('header_discount_percentage', 5, 2)->nullable(); 
            $table->decimal('header_discount_amount', 10, 2)->nullable(); 
            $table->decimal('expense_percentage', 5, 2)->nullable(); 
            $table->decimal('expense_amount', 10, 2)->nullable(); 
            $table->decimal('header_expense_percentage', 5, 2)->nullable(); 
            $table->decimal('header_expense_amount', 10, 2)->nullable(); 
            $table->decimal('net_value', 10, 2)->nullable();
            $table->decimal('sgst_percentage', 5, 2)->nullable(); 
            $table->decimal('cgst_percentage', 5, 2)->nullable(); 
            $table->decimal('igst_percentage', 5, 2)->nullable(); 
            $table->decimal('tax_value', 5, 2)->nullable(); 
            $table->decimal('taxable_amount', 5, 2)->nullable(); 
            $table->decimal('sub_total', 15, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_sale_order_items');
    }
};
