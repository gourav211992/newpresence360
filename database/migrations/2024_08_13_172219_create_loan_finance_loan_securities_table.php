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
        Schema::create('erp_loan_finance_loan_securities', function (Blueprint $table) {
            $table->id();
            $table->integer('vehicle_id');
            $table->string('own_capital')->nullable();
            $table->string('term_midc')->nullable();
            $table->string('finance_total')->nullable();
            $table->string('vehicle')->nullable();
            $table->string('collateral_security')->nullable();
            $table->string('security_total')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_loan_finance_loan_securities');
    }
};
