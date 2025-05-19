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
        Schema::create('erp_mo_item_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mo_id')->nullable();
            $table->unsignedBigInteger('mo_item_id')->nullable();
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
            $table->double('quantity', 20, 6)->default(0.00);
            $table->double('inventory_uom_qty', 20, 6)->default(0.00);
            $table->foreign('mo_item_id')->references('id')->on('erp_mo_items')->onDelete('cascade');
            $table->foreign('mo_id')->references('id')->on('erp_mfg_orders')->onDelete('cascade');

            $table->foreign('store_id')->references('id')->on('erp_stores')->onDelete('cascade');
            $table->foreign('rack_id')->references('id')->on('erp_racks')->onDelete('cascade');
            $table->foreign('shelf_id')->references('id')->on('erp_shelfs')->onDelete('cascade');
            $table->foreign('bin_id')->references('id')->on('erp_bins')->onDelete('cascade');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_mo_item_locations');
    }
};
