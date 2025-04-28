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
        Schema::create('erp_vendor_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id')->nullable()->index();
            $table->unsignedBigInteger('vendor_id')->nullable()->index();
            $table->string('item_code')->nullable()->index();
            $table->string('item_name')->nullable()->index();
            $table->string('part_number')->nullable();
            $table->text('item_details')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('item_id')->references('id')->on('erp_items')->onDelete('cascade');
            $table->foreign('vendor_id')->references('id')->on('erp_vendors')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_vendor_items');
    }
};
