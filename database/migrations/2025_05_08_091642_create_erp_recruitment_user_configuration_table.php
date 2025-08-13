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
        Schema::create('erp_recruitment_user_configuration', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('user_type')->nullable()->index();
            $table->boolean('current_opening')->default(1)->index(); 
            $table->boolean('interview_summary')->default(1)->index();   
            $table->boolean('my_scheduled')->default(1)->index();    
            $table->boolean('activity')->default(1)->index();  
            $table->boolean('new_applicants')->default(1)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_recruitment_user_configuration');
    }
};
