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
        Schema::create('mrn_mi_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mrn_header_id')->nullable();
            $table->unsignedBigInteger('mrn_detail_id')->nullable();
            $table->unsignedBigInteger('jo_product_id')->nullable();
            $table->unsignedBigInteger('jo_item_id')->nullable();
            $table->unsignedBigInteger('mi_item_id')->nullable();
            $table->double('mi_qty', 20, 6)->default(0);
            $table->double('mi_rate', 20, 6)->default(0);
            $table->double('mi_inventory_uom_qty', 20, 6)->default(0);
            $table->unsignedBigInteger('from_store_id')->nullable();
            $table->unsignedBigInteger('to_store_id')->nullable();
            $table->double('supplier_qty', 20, 6)->default(0);
            $table->double('consumed_qty', 20, 6)->default(0);
            $table->double('consumed_inventory_uom_qty', 20, 6)->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mrn_mi_mappings');
    }
};
