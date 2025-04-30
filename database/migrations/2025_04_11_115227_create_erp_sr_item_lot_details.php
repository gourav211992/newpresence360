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
        Schema::create('erp_sr_item_lot_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sr_item_id')->index();
            $table->string('lot_number', 150)->index();
            $table->double('lot_qty',20,6)->index();
            $table->date('original_receipt_date')->index();
            $table->double('inventory_uom_qty',20,6)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_sr_item_lot_details');
    }
};
