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
        Schema::create('erp_sale_order_item_attributes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_order_id')->nullable();
            $table->unsignedBigInteger('sale_order_item_id')->nullable();
            $table->string('item_code');
            $table->string('item_name');
            $table->string('attribute_name');
            $table->string('attribute_value');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_sale_order_item_attributes');
    }
};
