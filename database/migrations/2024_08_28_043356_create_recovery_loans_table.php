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
        Schema::create('erp_recovery_loans', function (Blueprint $table) {
            $table->id();
            $table->integer('home_loan_id');
            $table->string('recovery_series')->nullable();
            $table->string('document_no')->nullable();
            $table->string('application_no')->nullable();
            $table->string('recovery_amnnt')->nullable();
            $table->date('payment_date')->nullable();
            $table->string('rec_principal_amnt')->nullable();
            $table->string('rec_interest_amnt')->nullable();
            $table->string('payment_mode')->nullable();
            $table->string('ref_no')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_recovery_loans');
    }
};
