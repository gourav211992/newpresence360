<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Helpers\ConstantHelper;

return new class extends Migration
{ 
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('erp_sale_return_item_location_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id');
            $table->unsignedBigInteger('sale_return_id')->nullable();
            $table->unsignedBigInteger('sr_item_id')->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('item_code')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->string('store_code')->nullable();
            $table->unsignedBigInteger('rack_id')->nullable();
            $table->string('rack_code')->nullable();
            $table->unsignedBigInteger('shelf_id')->nullable();
            $table->string('shelf_code')->nullable();
            $table->unsignedBigInteger('bin_id')->nullable();
            $table->string('bin_code')->nullable();
            $table->double('returned_qty', 15, 2)->default(0.00);  // Quantity returned per location
            $table->double('inventory_uom_qty', 15, 2)->default(0.00);

            $table->foreign('sr_item_id')->references('id')->on('erp_sale_return_items')->onDelete('cascade');
            $table->foreign('sale_return_id')->references('id')->on('erp_sale_returns')->onDelete('cascade');

            $table->foreign('store_id')->references('id')->on('erp_stores')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('erp_items')->onDelete('cascade');
            $table->foreign('rack_id')->references('id')->on('erp_racks')->onDelete('cascade');
            $table->foreign('shelf_id')->references('id')->on('erp_shelfs')->onDelete('cascade');
            $table->foreign('bin_id')->references('id')->on('erp_bins')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_sale_return_item_location_histories');
    }
};
