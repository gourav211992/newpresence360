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
        Schema::create('erp_recovery_schedule_loans', function (Blueprint $table) {
            $table->id();
            $table->integer('home_loan_id');
            $table->string('period')->nullable();
            $table->string('principal_amnt')->nullable();
            $table->string('interest_rate')->nullable();
            $table->string('total')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_recovery_schedule_loans');
    }
};
