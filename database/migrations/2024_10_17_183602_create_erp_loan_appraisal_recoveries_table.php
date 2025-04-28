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
        Schema::create('erp_loan_appraisal_recoveries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('loan_appraisal_id')->constrained('erp_loan_appraisals')->onDelete('cascade');
            $table->string('year')->nullable();
            $table->string('start_amount')->nullable();
            $table->string('interest_amount')->nullable();
            $table->string('repayment_amount')->nullable();
            $table->string('end_amount')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_loan_appraisal_recoveries');
    }
};
