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
        Schema::create('erp_wh_item_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wh_structure_id')->nullable()->index();
            $table->unsignedBigInteger('store_id')->nullable()->index();
            $table->unsignedBigInteger('sub_store_id')->nullable()->index();
            $table->json('category_id')->nullable();
            $table->json('sub_category_id')->nullable();
            $table->json('item_id')->nullable();
            $table->json('structure_details')->nullable();
            $table->string('status', 191)->nullable()->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_wh_item_mappings');
    }
};
