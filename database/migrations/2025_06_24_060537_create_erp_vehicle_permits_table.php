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
        
        
        Schema::create('erp_vehicle_permits', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('vehicle_id')->nullable();

            $table->enum('type', ['1_year', '5_year'])->nullable(); 

            $table->string('permit_no', 50)->nullable();            
            $table->date('permit_date')->nullable();
            $table->date('permit_expiry_date')->nullable();

            $table->decimal('amount', 10, 2)->nullable(); 

            $table->unsignedBigInteger('attachment_id')->nullable(); 

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_vehicle_permits');
    }
};
