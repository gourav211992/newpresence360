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
        Schema::create('erp_pl_item_attributes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pl_id');
            $table->unsignedBigInteger('pl_item_id');
            $table->unsignedBigInteger('item_attribute_id');
            $table->string('item_code');
            $table->string('attribute_name');
            $table->unsignedBigInteger('attr_name');
            $table->string('attribute_value');
            $table->unsignedBigInteger('attr_value');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_pl_item_attributes');
    }
};
