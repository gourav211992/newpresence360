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
        Schema::create('erp_term_loan_promoters', function (Blueprint $table) {
            $table->id();
            $table->integer('term_loan_id');
            $table->string('promoter_name')->nullable();
            $table->string('domicile')->nullable();
            $table->string('domicile_photo')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_term_loan_promoters');
    }
};
