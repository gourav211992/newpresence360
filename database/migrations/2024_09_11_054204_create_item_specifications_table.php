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
        Schema::create('item_specifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id')->nullable()->index();
            $table->unsignedBigInteger('group_id')->nullable()->index();
            $table->unsignedBigInteger('specification_id')->nullable()->index();
            $table->string('specification_name')->nullable();
            $table->string('description')->nullable();
            $table->foreign('item_id')->references('id')->on('erp_items')->onDelete('set null');
            $table->foreign('group_id')->references('id')->on('erp_product_specifications')->onDelete('set null');
            $table->foreign('specification_id')->references('id')->on('erp_product_specification_details')->onDelete('set null');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_specifications');
    }
};
