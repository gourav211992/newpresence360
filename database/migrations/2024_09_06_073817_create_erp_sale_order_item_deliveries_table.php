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
        Schema::create('erp_sale_order_item_deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_order_id')->nullable();
            $table->unsignedBigInteger('sale_order_item_id')->nullable();
            $table->date('delivery_date')->nullable()->default(NULL);
            $table->double('quantity', 10,2)->nullable()->default(NULL);
            $table->double('invoice_quantity', 10,2)->nullable()->default(NULL);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_sale_order_item_deliveries');
    }
};
