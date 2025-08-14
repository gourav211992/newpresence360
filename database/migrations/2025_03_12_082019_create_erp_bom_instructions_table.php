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
        Schema::create('erp_bom_instructions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bom_id')->nullable()->index();
            $table->unsignedBigInteger('station_id')->nullable()->index();
            $table->unsignedBigInteger('section_id')->nullable()->index();
            $table->unsignedBigInteger('sub_section_id')->nullable()->index();
            $table->text('instructions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_bom_instructions');
    }
};
