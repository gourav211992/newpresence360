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
        Schema::create('erp_loan_guar_applicant_insurance_policies', function (Blueprint $table) {
            $table->id();
            $table->integer('loan_guar_applicant_id');
            $table->string('policy_no')->nullable();
            $table->date('maturity_date')->nullable();
            $table->string('sum_insured')->nullable();
            $table->string('co_branch')->nullable();
            $table->string('last_premium')->nullable();
            $table->string('surrender_value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_loan_guar_applicant_insurance_policies');
    }
};
