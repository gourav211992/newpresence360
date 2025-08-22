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
        Schema::create('erp_po_item_delivery', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id')->nullable();
            $table->unsignedBigInteger('po_item_id')->nullable();
            $table->double('qty', 15, 2)->default(0.00);
            $table->double('grn_qty', 15, 2)->default(0.00);
            $table->date('delivery_date');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('erp_po_item_delivery_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('purchase_order_id')->nullable();
            $table->unsignedBigInteger('po_item_id')->nullable();
            $table->double('qty', 15, 2)->default(0.00);
            $table->double('grn_qty', 15, 2)->default(0.00);
            $table->date('delivery_date');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_po_item_delivery_history');
        Schema::dropIfExists('po_item_delivery');
    }
};
