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
        Schema::create('erp_vendor_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id')->index();
            $table->foreign('vendor_id')->references('id')->on('erp_vendors');
            $table->unsignedBigInteger('store_id');
            $table->foreign('store_id')->references('id')->on('erp_stores');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_vendor_locations');
    }
};
