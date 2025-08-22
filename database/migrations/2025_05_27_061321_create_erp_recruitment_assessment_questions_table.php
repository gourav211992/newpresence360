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
        Schema::create('erp_recruitment_assessment_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable()->index();
            $table->unsignedBigInteger('assessment_id')->nullable()->index();
            $table->text('question')->nullable();
            $table->string('type')->nullable();
            $table->integer('score_from')->nullable();
            $table->integer('score_to')->nullable();
            $table->integer('low_score')->nullable();
            $table->integer('high_score')->nullable();
            $table->string('image')->nullable();
            $table->tinyInteger('is_dropdown')->nullable();
            $table->tinyInteger('is_required')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_recruitment_assessment_questions');
    }
};
