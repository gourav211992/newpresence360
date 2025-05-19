<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erp_loan_appraisal_credit_scoring', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loan_id');
            $table->unsignedBigInteger('loan_appraisal_id');
            $table->string('loan_type');
            $table->json('credit_data')->nullable();
            $table->json('document_completeness')->nullable();
            $table->json('basic_eligibility')->nullable();
            $table->json('collateral_credit_history')->nullable();
            $table->longText('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_loan_appraisal_credit_scoring');
    }
};
