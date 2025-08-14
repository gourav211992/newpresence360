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
        Schema::create('erp_loan_incomes', function (Blueprint $table) {
            $table->id();
            $table->integer('home_loan_id');
            $table->string('gross_monthly_income')->nullable();
            $table->string('net_monthly_income')->nullable();
            $table->string('encumbered');
            $table->string('plot_land')->nullable();
            $table->string('agriculture_land')->nullable();
            $table->string('house_godowns')->nullable();
            $table->string('others')->nullable();
            $table->string('estimated_value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_loan_incomes');
    }
};
