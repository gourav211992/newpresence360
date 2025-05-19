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
        Schema::table('erp_loan_appraisal_credit_scoring', function (Blueprint $table) {
            $table->json('financial_analysis')->nullable();
            $table->json('collateral_1')->nullable();
            $table->json('collateral_2')->nullable();
            $table->json('compliance_and_risk')->nullable();
            $table->json('community')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_loan_appraisal_credit_scoring', function (Blueprint $table) {
            $table->dropColumn('financial_analysis');
            $table->dropColumn('collateral_1');
            $table->dropColumn('collateral_2');
            $table->dropColumn('compliance_and_risk');
            $table->dropColumn('community');
        });
    }
};
