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
        Schema::create('erp_loan_disbursements', function (Blueprint $table) {
            $table->id();
            $table->integer('home_loan_id');
            $table->string('disbursal_series')->nullable();
            $table->string('disbursal_no')->nullable();
            $table->string('customer_contri')->nullable();
            $table->string('actual_dis')->nullable();
            $table->string('dis_remarks')->nullable();
            $table->integer('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_loan_disbursements');
    }
};
