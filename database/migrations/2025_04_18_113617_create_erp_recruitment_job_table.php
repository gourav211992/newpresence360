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
        Schema::create('erp_recruitment_job', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable()->index();
            $table->string('job_id',100)->nullable()->index();
            $table->unsignedBigInteger('job_title_id')->nullable()->index();
            $table->string('third_party_assessment')->nullable();
            $table->string('assessment_url')->nullable();
            $table->string('publish_for')->nullable();
            $table->string('publish_on')->nullable();
            $table->string('employement_type')->nullable();
            $table->unsignedBigInteger('industry_id')->nullable()->index();
            $table->string('work_mode')->nullable()->index();
            $table->unsignedBigInteger('location_id')->nullable()->index();
            $table->text('company_detail')->nullable();
            $table->unsignedBigInteger('assessment_id')->nullable()->index();
            $table->unsignedBigInteger('questionaries_id')->nullable()->index();
            $table->longText('job_description')->nullable();
            $table->unsignedBigInteger('education_id')->nullable()->index();
            $table->tinyInteger('work_exp_min')->nullable();
            $table->tinyInteger('work_exp_max')->nullable();
            $table->unsignedBigInteger('working_hour_id')->nullable();
            $table->decimal('annual_salary_min', 5, 2)->nullable();
            $table->decimal('annual_salary_max', 5, 2)->nullable();
            $table->unsignedBigInteger('notice_peroid_id')->nullable()->index();
            $table->string('status')->default('open')->index();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->string('created_by_type')->nullable()->index();
            $table->boolean('hide_from_candidate')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_recruitment_job');
    }
};
