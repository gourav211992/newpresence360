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
        Schema::create('erp_loan_employer_details', function (Blueprint $table) {
            $table->id();
            $table->integer('home_loan_id');
            $table->string('employer_name')->nullable();
            $table->string('department')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->integer('pin_code')->nullable();
            $table->string('phn_no')->nullable();
            $table->integer('ext_no')->nullable();
            $table->string('fax_num')->nullable();
            $table->string('company_email')->nullable();
            $table->string('designation')->nullable();
            $table->string('years_with_employers')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('previous_employer')->nullable();
            $table->string('retirement_age')->nullable();
            $table->string('other_assets')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_loan_employer_details');
    }
};
