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
        Schema::create('erp_loan_guarantor_co_applicant_term_deposits', function (Blueprint $table) {
            $table->id();
            $table->integer('loan_guarantor_co_applicant_id');
            $table->string('description')->nullable();
            $table->string('face_value')->nullable();
            $table->string('units')->nullable();
            $table->string('market_val')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_loan_guarantor_co_applicant_term_deposits');
    }
};
