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
        Schema::create('erp_loan_proposed_term_deposits', function (Blueprint $table) {
            $table->id();
            $table->integer('loan_proposed_loan_id');
            $table->string('post_office')->nullable();
            $table->date('instrument_date')->nullable();
            $table->string('face_value')->nullable();
            $table->string('resent_value')->nullable();
            $table->date('due_date')->nullable();
            $table->string('whether_encumbered')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_loan_proposed_term_deposits');
    }
};
