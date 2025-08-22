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
        Schema::create('erp_pslip_bom_consumptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pslip_id')->nullable();
            $table->unsignedBigInteger('pslip_item_id')->nullable();
            $table->unsignedBigInteger('bom_id')->nullable();
            $table->unsignedBigInteger('bom_detail_id')->nullable();
            $table->unsignedBigInteger('so_id')->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('item_code')->nullable();
            $table->json('attributes')->nullable();
            $table->unsignedBigInteger('uom_id')->nullable();
            $table->double('qty', 20,6)->default(0);
            $table->double('consumption_qty', 20,6)->default(0);
            $table->unsignedBigInteger('station_id')->nullable();
            $table->unsignedBigInteger('section_id')->nullable();
            $table->unsignedBigInteger('sub_section_id')->nullable();
            $table->double('rate', 20,4)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_pslip_bom_consumptions');
    }
};
