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
        Schema::create('erp_recruitment_job_interviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable()->index();
            $table->unsignedBigInteger('job_id')->nullable()->index();
            $table->unsignedBigInteger('candidate_id')->nullable()->index();
            $table->unsignedBigInteger('round_id')->nullable()->index();
            $table->timestamp('date_time')->nullable();
            $table->string('meeting_link',250)->nullable();
            $table->string('rating')->nullable();
            $table->string('behavior')->nullable();
            $table->string('skills')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('remarks')->nullable();
            $table->string('status')->nullable()->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('created_by_type')->nullable();
            $table->unsignedBigInteger('feedback_by')->nullable();
            $table->string('feedback_by_type')->nullable();
            $table->timestamp('feedback_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_recruitment_job_interviews');
    }
};
