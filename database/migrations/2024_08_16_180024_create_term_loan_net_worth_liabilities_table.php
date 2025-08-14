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
        Schema::create('erp_term_loan_net_worth_liabilities', function (Blueprint $table) {
            $table->id();
            $table->integer('term_loan_net_worth_id');
            $table->string('net_worth_desc')->nullable();
            $table->string('net_worth_value')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_term_loan_net_worth_liabilities');
    }
};
