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
        Schema::create('erp_sale_order_imports', function (Blueprint $table) {
            $table->id();
            $table->string('order_no', 50)->nullable();
            $table->date('document_date')->nullable();
            $table->string('customer_code', 100)->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('consignee_name', 100)->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('item_code', 100)->nullable();
            $table->unsignedBigInteger('uom_id')->nullable();
            $table->string('uom_code', 25)->nullable();
            $table->json('attributes')->nullable();
            $table->decimal('qty', 20, 6) -> default(0);
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
        Schema::dropIfExists('erp_sale_order_imports');
    }
};
