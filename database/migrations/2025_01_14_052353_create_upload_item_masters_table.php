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
        Schema::create('upload_item_masters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('item_name')->nullable();
            $table->string('item_code')->nullable();
            $table->string('category')->nullable();
            $table->string('subcategory')->nullable();
            $table->string('hsn')->nullable();
            $table->string('uom')->nullable();
            $table->enum('type', ['Goods', 'Service'])->nullable();
            $table->integer('min_stocking_level')->nullable();
            $table->integer('max_stocking_level')->nullable();
            $table->integer('reorder_level')->nullable();
            $table->integer('min_order_qty')->nullable();
            $table->integer('lead_days')->nullable();
            $table->integer('safety_days')->nullable();
            $table->integer('shelf_life_days')->nullable();
            $table->string('status')->default('Draft')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->json('attributes')->nullable();
            $table->json('specifications')->nullable();
            $table->json('alternate_uoms')->nullable();
            $table->string('sub_type')->nullable();
            $table->text('remarks')->nullable();
            $table->string('batch_no')->nullable();
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upload_item_masters');
    }
};
