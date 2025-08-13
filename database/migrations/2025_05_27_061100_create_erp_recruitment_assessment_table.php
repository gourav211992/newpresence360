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
        Schema::create('erp_recruitment_assessment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable()->index();
            $table->string('task_type')->nullable()->index();
            $table->unsignedBigInteger('job_title_id')->nullable()->index();
            $table->string('task_title')->nullable()->index();
            $table->string('passing_percentage')->nullable();
            $table->unsignedBigInteger('department_id')->nullable()->index();
            $table->unsignedBigInteger('designation_id')->nullable()->index();
            $table->text('description')->nullable();
            $table->tinyInteger('min_exp')->nullable();
            $table->tinyInteger('max_exp')->nullable();
            $table->tinyInteger('save_as_template')->nullable()->index();
            $table->string('template_name')->nullable()->index();
            $table->string('status')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_recruitment_assessment');
    }
};
