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
        Schema::create('erp_book_dynamic_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('book_id');
            $table->unsignedBigInteger('dynamic_field_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_book_dynamic_fields');
    }
};
