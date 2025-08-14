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
        Schema::create('erp_interest_rate_scores', function (Blueprint $table) {
            $table->id();
            $table->integer('interest_rate_id');
            $table->string('cibil_score_min')->nullable();
            $table->string('cibil_score_max')->nullable();
            $table->string('risk_cover')->nullable();
            $table->string('base_rate')->nullable();
            $table->string('interest_rate')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_interest_rate_scores');
    }
};
