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
        Schema::create('erp_vehicle_insurances', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('vehicle_id')->nullable();

            $table->string('policy_no', 50)->nullable(); 
            $table->string('insurance_company', 100)->nullable(); 

            $table->decimal('amount', 10, 2)->nullable();

            $table->date('insurance_date')->nullable();
            $table->date('insurance_expiry_date')->nullable();

            $table->unsignedBigInteger('attachment_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_vehicle_insurances');
    }
};
