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
        Schema::create('erp_loan_appraisal_disbursals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('loan_appraisal_id')->constrained('erp_loan_appraisals')->onDelete('cascade');
            $table->string('milestone')->nullable();
            $table->string('amount')->nullable();
            $table->string('remarks')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_loan_appraisal_disbursals');
    }
};
