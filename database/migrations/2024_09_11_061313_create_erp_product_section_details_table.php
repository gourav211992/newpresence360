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
        Schema::create('erp_product_section_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('station_id')->nullable()->index();
            $table->unsignedBigInteger('section_id')->nullable()->index();
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
            $table->foreign('section_id')->references('id')->on('erp_product_sections')->onDelete('cascade');
            $table->foreign('station_id')->references('id')->on('erp_stations')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_product_section_details');
    }
};
