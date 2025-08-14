<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Helpers\ConstantHelper;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('erp_addresses', function (Blueprint $table) {
            $table->id();
            $table->morphs('addressable'); 
            $table->unsignedBigInteger('country_id')->nullable()->index(); 
            $table->unsignedBigInteger('state_id')->nullable()->index();   
            $table->unsignedBigInteger('city_id')->nullable()->index();   
            $table->string('address')->nullable();               
            $table->enum('type', ConstantHelper::ADDRESS_TYPES)->nullable(); 
            $table->string('pincode', 10)->nullable();  ;                
            $table->string('phone', 15)->nullable();          
            $table->string('fax_number', 15)->nullable();
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_addresses');
    }
};
