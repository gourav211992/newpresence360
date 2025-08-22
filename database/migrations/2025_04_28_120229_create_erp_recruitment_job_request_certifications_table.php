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
        Schema::create('erp_recruitment_job_request_certifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_request_id')->nullable()->index('job_request_id_index');
            $table->unsignedBigInteger('certification_id')->nullable()->index('certification_id_index');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_recruitment_job_request_certifications');
    }
};
