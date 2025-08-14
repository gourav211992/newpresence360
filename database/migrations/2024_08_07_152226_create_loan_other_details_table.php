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
        Schema::create('erp_loan_other_details', function (Blueprint $table) {
            $table->id();
            $table->integer('home_loan_id');
            $table->integer('type')->comment('guarantor=1, co-applicant=2');
            $table->string('name')->nullable();
            $table->date('dob')->nullable();
            $table->string('fm_name')->nullable();
            $table->string('applicant_relation')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pin_code')->nullable();
            $table->string('occupation')->nullable();
            $table->string('phn_fax')->nullable();
            $table->string('email')->nullable();
            $table->string('pan_gir_no')->nullable();
            $table->string('net_annu_income')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_loan_other_details');
    }
};
