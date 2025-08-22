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
        
        Schema::create('erp_vehicle_media', function (Blueprint $table) {
            $table->id();

           
            $table->unsignedBigInteger('model_id')->nullable();
            $table->string('model_type', 100)->nullable();

            $table->uuid('uuid')->nullable()->unique();

            // Metadata
            $table->string('model_name', 100)->nullable();
            $table->string('collection_name', 50)->nullable();
            $table->string('name', 100)->nullable();
            $table->string('file_name', 255)->nullable();
            $table->string('mime_type', 50)->nullable();
            $table->string('disk', 100)->nullable();
            $table->string('conversions_disk', 100)->nullable();

            // File size
            $table->unsignedBigInteger('size')->nullable();

            // Optional JSON fields
            $table->json('manipulations')->nullable();
            $table->json('custom_properties')->nullable();
            $table->json('generated_conversions')->nullable();
            $table->json('responsive_images')->nullable();

            $table->unsignedInteger('order_column')->nullable()->index();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_vehicle_media');
    }
};
