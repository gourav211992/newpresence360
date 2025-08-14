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
        Schema::create('erp_transation_upload_items', function (Blueprint $table) {
            $table->id();
            $table->string('type', 191)->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('item_name', 291)->nullable();
            $table->string('item_code', 191)->nullable();
            $table->unsignedBigInteger('hsn_id')->nullable();
            $table->string('hsn_code', 191)->nullable();
            $table->unsignedBigInteger('uom_id')->nullable();
            $table->string('uom_code', 191)->nullable();
            $table->decimal('order_qty', 15,6)->default(0.000000)->nullable();
            $table->decimal('rate', 15,6)->default(0.000000)->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->string('store_code', 191)->nullable();
            $table->string('status', 191)->nullable();
            $table->string('form_status', 191)->nullable();
            $table->json('attributes')->nullable();
            $table->longText('reason')->nullable();
            $table->tinyInteger('is_error')->default(0)->nullable();
            $table->tinyInteger('is_sync')->default(0)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_transation_upload_items');
    }
};
