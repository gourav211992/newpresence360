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
        Schema::create('erp_loan_guar_applicant_legal_heirs', function (Blueprint $table) {
            $table->id();
            $table->integer('loan_guar_applicant_id');
            $table->string('name')->nullable();
            $table->string('relation')->nullable();
            $table->string('age')->nullable();
            $table->string('present_addr')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_loan_guar_applicant_legal_heirs');
    }
};
