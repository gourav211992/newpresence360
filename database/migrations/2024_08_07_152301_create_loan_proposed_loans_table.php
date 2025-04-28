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
        Schema::create('erp_loan_proposed_loans', function (Blueprint $table) {
            $table->id();
            $table->integer('home_loan_id');
            $table->string('outside_borrowing')->nullable();
            $table->string('loan_amount_request')->nullable();
            $table->string('interest_rate')->nullable();
            $table->string('floating_fixed')->nullable();
            $table->string('margin')->nullable();
            $table->longText('bank_name')->nullable();
            $table->longText('loan_credit')->nullable();
            $table->longText('security_schedule')->nullable();
            $table->longText('present_outstanding')->nullable();
            $table->longText('liabilities')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_loan_proposed_loans');
    }
};
