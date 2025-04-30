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
        Schema::create('erp_recruitment_job_candidates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable()->index();
            $table->unsignedBigInteger('job_id')->nullable()->index();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('mobile_no')->nullable();
            $table->unsignedBigInteger('education_id')->nullable()->index();
            $table->tinyInteger('work_exp')->nullable();
            $table->string('current_organization')->nullable();
            $table->string('exp_salary')->nullable();
            $table->unsignedBigInteger('location_id')->nullable()->index();
            $table->string('status')->nullable();
            $table->string('potential_type')->nullable();
            $table->string('resume_path')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->string('created_by_type')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_recruitment_job_candidates');
    }
};
