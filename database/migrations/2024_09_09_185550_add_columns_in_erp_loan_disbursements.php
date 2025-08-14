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
        Schema::table('erp_loan_disbursements', function (Blueprint $table) {
            $table->string('dis_amount')->nullable();
            $table->string('dis_milestone')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_loan_disbursements', function (Blueprint $table) {
            $table->dropColumn(['dis_amount', 'dis_milestone']);
        });
    }
};
