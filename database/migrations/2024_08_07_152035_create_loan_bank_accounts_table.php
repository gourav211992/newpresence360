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
        Schema::create('erp_loan_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->integer('home_loan_id');
            $table->string('bank_name')->nullable();
            $table->string('branch')->nullable();
            $table->integer('ac_held')->nullable();
            $table->string('ac_type')->nullable();
            $table->string('ac_no')->nullable();
            $table->string('ac_balance')->nullable();
            $table->date('date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_loan_bank_accounts');
    }
};
