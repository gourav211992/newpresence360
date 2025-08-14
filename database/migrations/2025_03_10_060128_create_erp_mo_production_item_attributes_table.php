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
        Schema::create('erp_mo_production_item_attributes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mo_id')->index();
            $table->unsignedBigInteger('mo_production_item_id')->index();
            $table->unsignedBigInteger('item_attribute_id')->index();
            $table->unsignedBigInteger('item_id')->index();
            $table->unsignedBigInteger('item_code')->index();
            $table->unsignedBigInteger('attribute_group_id')->index();
            $table->unsignedBigInteger('attribute_id')->index();
            $table->unsignedBigInteger('attribute_name')->nullable();
            $table->unsignedBigInteger('attribute_value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_mo_production_item_attributes');
    }
};
