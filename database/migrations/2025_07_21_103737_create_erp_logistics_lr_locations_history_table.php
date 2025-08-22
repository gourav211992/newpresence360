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
        Schema::create('erp_logistics_lr_locations_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('lorry_receipt_id')->nullable();

            // Fields from the Blade inputs
            $table->unsignedBigInteger('location_id')->nullable();
            $table->string('type',20)->nullable();
            $table->integer('no_of_articles')->nullable();
            $table->decimal('weight', 10, 2)->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_logistics_lr_locations_history');
    }
};
