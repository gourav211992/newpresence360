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
        Schema::create('erp_pl_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pl_header_id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('order_item_id');
            $table->unsignedBigInteger('order_item_delivery_id');
            $table->unsignedBigInteger('item_id');
            $table->string('item_code');
            $table->string('item_name');
            $table->unsignedBigInteger('hsn_id')->nullable();
            $table->string('hsn_code')->nullable();
            $table->unsignedBigInteger('uom_id')->nullable();
            $table->string('uom_code')->nullable();
            $table->decimal('order_qty', 20,6);
            $table->decimal('picked_qty', 20,6);
            $table->unsignedBigInteger('inventory_uom_id')->nullable();
            $table->string('inventory_uom_code',100)->nullable();
            $table->decimal('inventory_uom_qty', 20, 6);
            $table->date('delivery_date');
            $table->decimal('rate', 20, 6);
            $table->decimal('total_amount', 20, 6);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_pl_items');
    }
};
