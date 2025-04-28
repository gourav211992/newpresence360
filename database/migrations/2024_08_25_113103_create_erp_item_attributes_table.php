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
        Schema::create('erp_item_attributes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id')->nullable()->index();
            $table->unsignedBigInteger('attribute_group_id')->nullable()->index();
            $table->json('attribute_id')->nullable();
            $table->boolean('required_bom')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('item_id')->references('id')->on('erp_items')->onDelete('set null');
            $table->foreign('attribute_group_id')->references('id')->on('erp_attribute_groups')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_item_attributes', function (Blueprint $table) {
            Schema::dropIfExists('erp_item_attributes');
        });

       
    }
};
