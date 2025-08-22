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
        Schema::create('erp_sale_order_import_shufab', function (Blueprint $table) {
            $table->id();
            $table->string('order_no')->nullable();
            $table->date('document_date')->nullable();
            $table->string('customer_code')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('consignee_name')->nullable();
            $table->string('item_id')->nullable();
            $table->string('item_code')->nullable();
            $table->unsignedBigInteger('uom_id')->nullable();
            $table->string('uom_code')->nullable();
            $table->decimal('size_1', 20, 6) -> default(0);
            $table->decimal('size_2', 20, 6) -> default(0);
            $table->decimal('size_3', 20, 6) -> default(0);
            $table->decimal('size_4', 20, 6) -> default(0);
            $table->decimal('size_5', 20, 6) -> default(0);
            $table->decimal('size_6', 20, 6) -> default(0);
            $table->decimal('size_7', 20, 6) -> default(0);
            $table->decimal('size_8', 20, 6) -> default(0);
            $table->decimal('size_9', 20, 6) -> default(0);
            $table->decimal('size_10', 20, 6) -> default(0);
            $table->decimal('size_11', 20, 6) -> default(0);
            $table->decimal('size_12', 20, 6) -> default(0);
            $table->decimal('size_13', 20, 6) -> default(0);
            $table->decimal('size_14', 20, 6) -> default(0);
            $table->decimal('rate', 20, 6)->default(0)->nullable();
            $table->date('delivery_date')->nullable();
            $table->date('remarks')->nullable();
            $table->enum('is_migrated', [0,1])->default(0);
            $table->json('reason')->nullable();    
            $table->timestamps();

            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_sale_order_import_shufab');
    }
};
