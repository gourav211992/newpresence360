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

        Schema::create('erp_recruitment_assessment_question_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable()->index('organization_id_index');
            $table->unsignedBigInteger('assessment_id')->nullable()->index('assessment_id_index');
            $table->unsignedBigInteger('assessment_question_id')->nullable()->index('assessment_question_id_index');
            $table->text('option')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_recruitment_assessment_question_options');
    }
};
