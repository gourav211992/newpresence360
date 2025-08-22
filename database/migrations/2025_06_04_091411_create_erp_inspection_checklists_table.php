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
        Schema::create('erp_inspection_checklists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('status', 10)->default('active');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('erp_inspection_checklist_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('header_id');
            $table->string('name');
            $table->string('data_type')->default('string');
            $table->string('description')->nullable();
            $table->boolean('mandatory')->default(false); 
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('erp_inspection_checklist_detail_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inspection_checklist_detail_id')->nullable(); 
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
        Schema::dropIfExists('erp_inspection_checklist_detail_values');
        Schema::dropIfExists('erp_inspection_checklist_details');
        Schema::dropIfExists('erp_inspection_checklists');
    }
};
