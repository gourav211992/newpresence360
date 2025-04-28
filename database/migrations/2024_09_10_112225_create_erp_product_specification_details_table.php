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
        Schema::create('erp_product_specification_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_specification_id')->nullable()->index;
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            // $table->foreign('product_specification_id')->references('id')->on('erp_product_specifications')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_product_specification_details');
    }
};
