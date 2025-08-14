<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $commonColumns = function (Blueprint $table) {
            $table->unsignedBigInteger('ti_invoice_id')->nullable();
            $table->string('lr_id')->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->unsignedBigInteger('customer_item_id')->nullable();
            $table->string('customer_item_code', 100)->nullable();
            $table->string('customer_item_name', 200)->nullable();
            $table->string('item_code')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->unsignedBigInteger('sub_store_id')->nullable();
            $table->string('item_name')->nullable();
            $table->unsignedBigInteger('hsn_id')->nullable();
            $table->string('hsn_code')->nullable();
            $table->unsignedBigInteger('uom_id')->nullable();
            $table->string('uom_code')->nullable();
            $table->decimal('order_qty', 20, 6)->nullable();
            $table->unsignedBigInteger('ti_order_id')->nullable();
            $table->unsignedBigInteger('dn_id')->nullable()->comment('From which header this has been pulled');
            $table->unsignedBigInteger('invoice_id')->nullable()->comment('From which header this has been pulled');
            $table->decimal('invoice_qty', 20, 6)->nullable();
            $table->unsignedBigInteger('inventory_uom_id')->nullable();
            $table->string('inventory_uom_code')->nullable();
            $table->decimal('inventory_uom_qty', 20, 6)->nullable();
            $table->decimal('rate', 20, 6)->nullable();
            $table->decimal('item_discount_amount', 15, 2)->default(0.00);
            $table->decimal('header_discount_amount', 15, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('item_expense_amount', 15, 2)->default(0.00);
            $table->decimal('header_expense_amount', 15, 2)->default(0.00);
            $table->decimal('total_item_amount', 15, 2)->default(0.00);
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        };

        Schema::create('erp_ti_invoice_items', function (Blueprint $table) use ($commonColumns) {
            $table->bigIncrements('id');
            $commonColumns($table);
        });

        Schema::create('erp_ti_invoice_items_history', function (Blueprint $table) use ($commonColumns) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('source_id');
            $commonColumns($table);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_ti_invoice_items_history');
        Schema::dropIfExists('erp_ti_invoice_items');
    }
};
