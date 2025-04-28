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
        Schema::create('erp_disbursal_loans', function (Blueprint $table) {
            $table->id();
            $table->integer('home_loan_id');
            $table->string('milestone')->nullable();
            $table->string('dis_amount');
            $table->string('dis_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_disbursal_loans');
    }
};
