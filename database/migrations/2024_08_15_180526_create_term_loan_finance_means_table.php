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
        Schema::create('erp_term_loan_finance_means', function (Blueprint $table) {
            $table->id();
            $table->integer('term_loan_id');
            $table->string('promoters_cont')->nullable();
            $table->string('equity_total')->nullable();
            $table->string('midc_ltd')->nullable();
            $table->string('others')->nullable();
            $table->string('debt_total')->nullable();
            $table->string('grand_total')->nullable();
            $table->string('guarantee_detail')->nullable();
            $table->string('period_state')->nullable();
            $table->string('primary_land')->nullable();
            $table->string('primary_building')->nullable();
            $table->string('primary_machinery')->nullable();
            $table->string('primary_other')->nullable();
            $table->string('primary_total')->nullable();
            $table->string('collateral_land')->nullable();
            $table->string('collateral_building')->nullable();
            $table->string('collateral_machinery')->nullable();
            $table->string('collateral_other')->nullable();
            $table->string('collateral_total')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_term_loan_finance_means');
    }
};
