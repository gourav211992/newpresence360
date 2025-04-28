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
        Schema::create('erp_loan_settlements', function (Blueprint $table) {
            $table->id();
            $table->integer('home_loan_id');
            $table->string('settle_series')->nullable();
            $table->string('settle_document_no')->nullable();
            $table->string('settle_application_no')->nullable();
            $table->string('settle_bal_loan_amnnt')->nullable();
            $table->date('settle_prin_bal_amnnt')->nullable();
            $table->string('settle_intr_bal_amnnt')->nullable();
            $table->string('settle_amnnt')->nullable();
            $table->string('settle_wo_amnnt')->nullable();
            $table->string('remarks')->nullable();
            $table->integer('status')->nullable();
            $table->string('settle_appr_status')->comment('approve=1, reject=2')->nullable();
            $table->string('settle_appr_remark')->nullable();
            $table->string('settle_appr_doc')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_loan_settlements');
    }
};
