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
        Schema::create('erp_loan_proposed_moveable_assets', function (Blueprint $table) {
            $table->id();
            $table->integer('loan_proposed_loan_id');
            $table->string('description')->nullable();
            $table->string('acquiring_year')->nullable();
            $table->string('purchase_price')->nullable();
            $table->string('present_market_val')->nullable();
            $table->string('valuation_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_loan_proposed_moveable_assets');
    }
};
