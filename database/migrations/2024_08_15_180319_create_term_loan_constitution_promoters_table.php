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
        Schema::create('erp_term_loan_constitution_promoters', function (Blueprint $table) {
            $table->id();
            $table->integer('term_loan_constitution_id');
            $table->string('sister_concern')->nullable();
            $table->string('banker_name')->nullable();
            $table->string('nature_facility_address')->nullable();
            $table->string('outstanding')->nullable();
            $table->string('any_default')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_term_loan_constitution_promoters');
    }
};
