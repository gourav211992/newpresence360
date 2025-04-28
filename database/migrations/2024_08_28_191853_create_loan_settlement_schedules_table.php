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
        Schema::create('erp_loan_settlement_schedules', function (Blueprint $table) {
            $table->id();
            $table->integer('loan_settlement_id');
            $table->date('schedule_date')->nullable();
            $table->string('schedule_amnt_type')->nullable();
            $table->string('schedule_loan_prcnt')->nullable();
            $table->string('schedule_amnt')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_loan_settlement_schedules');
    }
};
