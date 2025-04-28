<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('erp_bank_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bank_id')->nullable(); 
            $table->string('account_number')->nullable();
            $table->string('branch_name')->nullable(); 
            $table->string('branch_address')->nullable();
            $table->string('ifsc_code')->nullable(); 
            $table->timestamps();
            $table->foreign('bank_id')->references('id')->on('erp_banks')->onDelete('cascade');
            $table->index('bank_id');
            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_bank_details');
    }
};
