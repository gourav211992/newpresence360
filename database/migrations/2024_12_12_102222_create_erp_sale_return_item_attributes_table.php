<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Helpers\ConstantHelper;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('erp_sale_return_item_attributes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_return_id')->nullable();
            $table->unsignedBigInteger('sale_return_item_id')->nullable();
            $table->unsignedBigInteger('item_attribute_id')->nullable()->comment('Reference to erp_item_attributes');
            $table->string('item_code')->nullable();
            $table->string('attribute_name')->nullable();
            $table->string('attr_name')->nullable();
            $table->string('attribute_value')->nullable();
            $table->string('attr_value')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->foreign('sale_return_item_id')->references('id')->on('erp_sale_return_items')->onDelete('cascade');
            $table->foreign('sale_return_id')->references('id')->on('erp_sale_returns')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
            ;
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_sale_return_item_attributes');
    }
};
