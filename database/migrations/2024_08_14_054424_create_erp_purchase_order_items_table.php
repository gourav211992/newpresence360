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
        Schema::create('erp_purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id')->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('hsn_code')->nullable();
            $table->unsignedBigInteger('uom_id')->nullable();
            $table->date('expected_delivery_date')->nullable();
            $table->decimal('quantity', 10, 2)->nullable(); 
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
            $table->boolean('selected_item')->default(false);
            $table->timestamps(); 
            $table->softDeletes();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_purchase_order_items');
    }
};
