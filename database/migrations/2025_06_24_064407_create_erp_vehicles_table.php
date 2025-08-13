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
      
       
        Schema::create('erp_vehicles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('transporter_id')->nullable();

            $table->string('lorry_no', 30)->nullable();            
            $table->unsignedBigInteger('vehicle_type_id')->nullable();         
            $table->string('chassis_no', 50)->nullable();          
            $table->string('engine_no', 50)->nullable();
            $table->string('rc_no', 50)->nullable();    
            $table->string('rto_no', 50)->nullable();            

            $table->string('company_name', 100)->nullable();       
            $table->string('model_name', 100)->nullable();         
            $table->integer('capacity_kg')->default(0)->nullable();

            $table->unsignedBigInteger('driver_id')->nullable();

            $table->string('fuel_type', 30)->nullable();            
            $table->date('purchase_date')->nullable();
            $table->string('ownership', 50)->nullable();            

            $table->unsignedBigInteger('vehicle_attachment')->nullable(); 
            $table->unsignedBigInteger('vehicle_video')->nullable();      
            $table->unsignedBigInteger('attachment_id')->nullable();
            $table->string('status', 20)->nullable();  

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_vehicles');
    }
};
