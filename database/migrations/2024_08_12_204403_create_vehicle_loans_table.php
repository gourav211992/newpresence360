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
        Schema::create('erp_vehicle_loans', function (Blueprint $table) {
            $table->id();
            $table->integer('vehicle_id');
            $table->string('name')->nullable();
            $table->string('address')->nullable();
            $table->string('father_name')->nullable();
            $table->string('qualification')->nullable();
            $table->string('investment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_vehicle_loans');
    }
};
