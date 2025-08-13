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
        Schema::dropIfExists('mrn_mi_mappings');

        Schema::create('erp_mrn_jo_items', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->unsignedBigInteger('mrn_header_id')->nullable();
            $table->unsignedBigInteger('mrn_detail_id')->nullable();
            $table->unsignedBigInteger('jo_product_id')->nullable();
            $table->unsignedBigInteger('jo_item_id')->nullable();
            $table->unsignedBigInteger('mi_item_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->unsignedBigInteger('sub_store_id')->nullable();
            $table->double('consumed_qty', 20, 6)->default(0);
            $table->double('inventory_uom_qty', 20, 6)->default(0);
            $table->double('cost_per_unit', 20, 6)->default(0);
            $table->double('total_cost', 20, 6)->default(0);
            $table->string('status')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_mrn_jo_items');
    }
};
