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
        Schema::create('erp_pl_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pl_header_id');
            $table->unsignedBigInteger('item_id');
            $table->string('item_code');
            $table->string('item_name');
            $table->json('attributes')->nullable();
            $table->unsignedBigInteger('inventory_uom_id');
            $table->string('inventory_uom_code',100);
            $table->decimal('inventory_uom_qty', 20, 6);
            $table->timestamps();
        });
        Schema::create('erp_pl_items_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id');
            $table->unsignedBigInteger('pl_header_id');
            $table->unsignedBigInteger('item_id');
            $table->string('item_code');
            $table->string('item_name');
            $table->json('attributes')->nullable();
            $table->unsignedBigInteger('inventory_uom_id');
            $table->string('inventory_uom_code',100);
            $table->decimal('inventory_uom_qty', 20, 6);
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_pl_items_history');
        Schema::dropIfExists('erp_pl_items');
    }
};
