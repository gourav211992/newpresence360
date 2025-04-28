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
        Schema::create('erp_term_loan_documents', function (Blueprint $table) {
            $table->id();
            $table->integer('term_loan_id');
            $table->string('adhar_card')->nullable();
            $table->string('gir_no')->nullable();
            $table->string('asset_proof')->nullable();
            $table->string('application')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_term_loan_documents');
    }
};
