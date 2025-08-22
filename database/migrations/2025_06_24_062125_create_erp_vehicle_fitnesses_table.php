<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erp_vehicle_fitnesses', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->string('fitness_no', 50)->nullable();
            $table->date('fitness_date')->nullable();
            $table->date('fitness_expiry_date')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->unsignedBigInteger('attachment_id')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_vehicle_fitnesses');
    }
};
