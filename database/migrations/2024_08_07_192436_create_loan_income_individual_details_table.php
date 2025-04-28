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
        Schema::create('erp_loan_income_individual_details', function (Blueprint $table) {
            $table->id();
            $table->integer('loan_income_id');
            $table->string('source')->nullable();
            $table->string('purpose')->nullable();
            $table->date('sanction_date')->nullable();
            $table->string('amount')->nullable();
            $table->string('outstanding')->nullable();
            $table->string('emi')->nullable();
            $table->string('overdue_amount')->nullable();
            $table->date('overdue_since')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_loan_income_individual_details');
    }
};
