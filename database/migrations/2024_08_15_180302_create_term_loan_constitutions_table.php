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
        Schema::create('erp_term_loan_constitutions', function (Blueprint $table) {
            $table->id();
            $table->integer('term_loan_id');
            $table->string('business_type')->nullable();
            $table->date('prc')->nullable();
            $table->date('esclation')->nullable();
            $table->string('sia_no')->nullable();
            $table->date('sia_date')->nullable();
            $table->string('director_name')->nullable();
            $table->string('working_capital')->nullable();
            $table->date('capital_facilities')->nullable();
            $table->string('site_dev')->nullable();
            $table->string('civil_works')->nullable();
            $table->string('plant_install')->nullable();
            $table->string('technical_fee')->nullable();
            $table->string('fixed_asset')->nullable();
            $table->string('pre_operative')->nullable();
            $table->string('provision')->nullable();
            $table->string('startup_expense')->nullable();
            $table->string('margin_money')->nullable();
            $table->string('total')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_term_loan_constitutions');
    }
};
