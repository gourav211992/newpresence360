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
        Schema::create('erp_sale_return_item_attribute_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('sale_return_id')->nullable();
            $table->unsignedBigInteger('return_item_id')->nullable();
            $table->unsignedBigInteger('item_attribute_id')->nullable()->comment('use tbl erp_item_attributes');
            $table->string('item_code')->nullable();
            $table->string('attribute_name')->nullable();
            $table->unsignedBigInteger('attr_name')->nullable();
            $table->string('attribute_value')->nullable();
            $table->unsignedBigInteger('attr_value')->nullable();


            $table->foreign('id')->references('id')->on('erp_sale_return_item_attributes')->onDelete('cascade');
            $table->foreign('return_item_id')->references('id')->on('erp_sale_return_items_histories')->onDelete('cascade');
            $table->foreign('sale_return_id')->references('id')->on('erp_sale_return_histories')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_sale_return_item_attribute_histories');
    }
};
