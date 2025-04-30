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
        Schema::create('erp_bom_production_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bom_id')->nullable()->index();
            $table->unsignedBigInteger('station_id')->nullable()->index();
            $table->unsignedBigInteger('item_id')->nullable()->index();
            $table->string('item_code')->nullable()->index();
            $table->json('attributes')->nullable();
            $table->unsignedBigInteger('uom_id')->nullable()->index();
            $table->double('qty',[20,6])->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_bom_production_items');
    }
};
