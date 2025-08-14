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
    public function up()
    {
        Schema::create('erp_items', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ConstantHelper::ITEM_TYPES)->default(ConstantHelper::GOODS);
            $table->unsignedBigInteger('unit_id')->nullable()->index();
            $table->unsignedBigInteger('hsn_id')->nullable()->index();
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->unsignedBigInteger('subcategory_id')->nullable()->index();
            $table->unsignedBigInteger('uom_id')->nullable()->index();
            $table->string('item_code')->nullable()->index();
            $table->string('item_name')->nullable()->index();
            $table->integer('min_stocking_level')->nullable();
            $table->integer('max_stocking_level')->nullable();
            $table->integer('reorder_level')->nullable();
            $table->integer('minimum_order_qty')->nullable();
            $table->integer('lead_days')->nullable();
            $table->integer('safety_days')->nullable();
            $table->integer('shelf_life_days')->nullable();
            $table->enum('status', ConstantHelper::STATUS)->default(ConstantHelper::ACTIVE);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('unit_id')->references('id')->on('erp_units')->onDelete('cascade');
            $table->foreign('hsn_id')->references('id')->on('erp_hsns')->onDelete('set null');
            $table->foreign('category_id')->references('id')->on('erp_categories')->onDelete('set null');
            $table->foreign('subcategory_id')->references('id')->on('erp_categories')->onDelete('set null');
            $table->foreign('uom_id')->references('id')->on('erp_units')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_items');
    }
};
