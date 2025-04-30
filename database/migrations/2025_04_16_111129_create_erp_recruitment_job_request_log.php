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
        Schema::create('erp_recruitment_job_request_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable()->index();
            $table->unsignedBigInteger('job_request_id')->nullable()->index();
            $table->unsignedBigInteger('next_approval_authority')->nullable()->index();
            $table->text('log_message')->nullable();
            $table->string('status')->nullable()->index();
            $table->unsignedBigInteger('action_by')->nullable()->index();
            $table->string('action_by_type')->default('employee')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_recruitment_job_request_log');
    }
};
