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
        Schema::create('erp_dynamic_field_detail_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dynamic_field_detail_id')->nullable(); 
            $table->string('value')->nullable(); 
            $table->timestamps(); 
            $table->softDeletes(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_dynamic_field_detail_values');
    }
};
