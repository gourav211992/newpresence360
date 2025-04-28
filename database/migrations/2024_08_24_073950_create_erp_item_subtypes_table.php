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
        Schema::create('erp_item_subtypes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('erp_items');
            $table->foreignId('sub_type_id')->constrained('erp_sub_types');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_item_subtypes');
    }
};
