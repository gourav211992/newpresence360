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
        Schema::create('erp_loan_appraisals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('loan_id')->constrained('erp_home_loans')->onDelete('cascade');
            $table->string('application_no')->nullable();
            $table->string('unit_name')->nullable();
            $table->string('proprietor_name')->nullable();
            $table->string('address')->nullable();
            $table->string('project_cost')->nullable();
            $table->string('term_loan')->nullable();
            $table->string('promotor_contribution')->nullable();
            $table->string('cibil_score')->nullable();
            $table->string('interest_rate')->nullable();
            $table->string('loan_period')->nullable();
            $table->string('repayment_type')->nullable();
            $table->string('no_of_installments')->nullable();
            $table->string('repayment_start_after')->nullable();
            $table->string('repayment_start_period')->nullable();
            $table->string('status')->nullable();

            $table->unsignedBigInteger('group_id')->index();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('organization_id')->index();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_loan_appraisals');
    }
};
