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
        Schema::create('erp_pslip_consumption_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pslip_id')->nullable()->index();
            $table->unsignedBigInteger('pslip_consumption_id')->nullable()->index();
            $table->unsignedBigInteger('item_id')->nullable()->index();
            $table->unsignedBigInteger('store_id')->nullable()->index();
            $table->unsignedBigInteger('sub_store_id')->nullable()->index();
            $table->unsignedBigInteger('station_id')->nullable()->index();
            $table->unsignedBigInteger('rack_id')->nullable()->index();
            $table->unsignedBigInteger('shelf_id')->nullable()->index();
            $table->unsignedBigInteger('bin_id')->nullable()->index();
            $table->double('quantity',[20,6])->default(0);
            $table->double('inventory_uom_qty',[20,6])->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_pslip_consumption_locations');
    }
};
