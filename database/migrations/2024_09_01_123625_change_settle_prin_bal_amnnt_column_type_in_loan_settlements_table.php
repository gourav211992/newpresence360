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
        Schema::table('erp_loan_settlements', function (Blueprint $table) {
            $table->string('settle_prin_bal_amnnt')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_loan_settlements', function (Blueprint $table) {
            $table->date('settle_prin_bal_amnnt')->change();
        });
    }
};
