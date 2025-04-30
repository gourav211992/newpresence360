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
        Schema::create('erp_mi_item_lot_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mi_item_id')->index();
            $table->string('lot_number', 150)->index();
            $table->double('lot_qty',20,6);
            $table->double('total_lot_qty', 20, 6);
            $table->date('original_receipt_date');
            $table->double('inventory_uom_qty',20,6);
            $table->timestamps();
        });
        Schema::create('erp_mi_item_lot_details_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id');
            $table->unsignedBigInteger('mi_item_id')->index();
            $table->string('lot_number', 150)->index();
            $table->double('lot_qty',20,6);
            $table->double('total_lot_qty', 20, 6);
            $table->date('original_receipt_date');
            $table->double('inventory_uom_qty',20,6);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_mi_item_lot_details_history');
        Schema::dropIfExists('erp_mi_item_lot_details');
    }
};
