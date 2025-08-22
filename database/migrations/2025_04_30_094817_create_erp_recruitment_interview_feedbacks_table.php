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
        Schema::create('erp_recruitment_interview_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_id')->nullable()->index();
            $table->unsignedBigInteger('interview_id')->nullable();
            $table->unsignedBigInteger('round_id')->nullable()->index();
            $table->unsignedBigInteger('panel_id')->nullable();
            $table->unsignedBigInteger('candidate_id')->nullable();
            $table->string('rating')->nullable();
            $table->string('behavior')->nullable();
            $table->string('skills')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_recruitment_interview_feedbacks');
    }
};
