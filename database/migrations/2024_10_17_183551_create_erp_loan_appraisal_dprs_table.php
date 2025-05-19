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
        Schema::create('erp_loan_appraisal_dprs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('loan_appraisal_id')->constrained('erp_loan_appraisals')->onDelete('cascade');
            $table->foreignId('dpr_template_id')->constrained('erp_dpr_template_masters')->onDelete('cascade');
            $table->foreignId('dpr_id')->constrained('erp_dpr_masters')->onDelete('cascade');
            $table->string('dpr_value')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_loan_appraisal_dprs');
    }
};
