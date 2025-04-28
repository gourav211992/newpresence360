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
        Schema::create('erp_loan_proposed_insurance_policies', function (Blueprint $table) {
            $table->id();
            $table->integer('loan_proposed_loan_id');
            $table->string('policy_no')->nullable();
            $table->date('issuance_date')->nullable();
            $table->string('sum_insured')->nullable();
            $table->string('co_branch')->nullable();
            $table->string('annual_premium')->nullable();
            $table->string('premium_paid')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_loan_proposed_insurance_policies');
    }
};
