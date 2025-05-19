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
        Schema::create('erp_sale_return_items_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('sale_return_id');
            $table->unsignedBigInteger('sr_item_id')->nullable()->comment('erp_sr_item_id');
            $table->unsignedBigInteger('item_id');
            $table->string('item_code');
            $table->string('item_name')->nullable();
            $table->unsignedBigInteger('hsn_id');
            $table->string('hsn_code')->nullable();
            $table->unsignedBigInteger('uom_id');
            $table->string('uom_code');
            $table->unsignedBigInteger('inventory_uom_id')->nullable();
            $table->string('inventory_uom_code')->nullable();
            $table->decimal('inventory_uom_qty',15, 2)->default(0.00);
            $table->decimal('rate', 15, 2)->default(0.00);
            $table->decimal('item_discount_amount', 15, 2)->default(0.00);
            $table->decimal('header_discount_amount', 15, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('item_expense_amount', 15, 2)->default(0.00);
            $table->decimal('header_expense_amount', 15, 2)->default(0.00);
            $table->decimal('total_item_amount', 15, 2)->default(0.00);
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->foreign('sale_return_id')->references('id')->on('erp_sale_returns')->onDelete('cascade');
            $table->foreign('sr_item_id')->references('id')->on('erp_sale_return_items')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_sale_return_items_histories');
    }
};
