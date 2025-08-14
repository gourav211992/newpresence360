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
        Schema::create('erp_loan_guar_applicant_moveable_assets', function (Blueprint $table) {
            $table->id();
            $table->integer('loan_guar_applicant_id');
            $table->string('description')->nullable();
            $table->string('purchase_price')->nullable();
            $table->string('market_val')->nullable();
            $table->date('valuation_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_loan_guar_applicant_moveable_assets');
    }
};
