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
        Schema::create('erp_recruitment_job_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable()->index();
            $table->string('job_type',100)->nullable();
            $table->unsignedBigInteger('emp_id')->nullable()->index();
            $table->unsignedBigInteger('job_title_id')->nullable()->index();
            $table->string('job_id',100)->nullable()->index();
            $table->string('request_id',100)->nullable()->index();
            $table->string('no_of_position',100)->nullable();
            $table->unsignedBigInteger('education_id')->nullable()->index();
            $table->unsignedBigInteger('certification_id')->nullable()->index();
            $table->unsignedBigInteger('work_exp_id')->nullable()->index();
            $table->date('expected_doj')->nullable()->index();
            $table->string('priority')->nullable()->index();
            $table->unsignedBigInteger('location_id')->nullable()->index();
            $table->longText('job_description')->nullable();
            $table->text('reason')->nullable();
            $table->string('assessment_required')->nullable();
            $table->unsignedBigInteger('approval_authority')->nullable()->index();
            $table->string('status')->default('pending')->index();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->string('created_by_type')->default('employee')->index();
            $table->timestamp('approved_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_recruitment_job_requests');
    }
};
