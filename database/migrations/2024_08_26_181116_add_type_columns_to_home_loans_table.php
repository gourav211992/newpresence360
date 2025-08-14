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
        Schema::table('erp_home_loans', function (Blueprint $table) {
            $table->string('recovery_interest')->nullable();
            $table->string('recovery_sentioned')->nullable();
            $table->string('recovery_repayment_type')->nullable();
            $table->string('recovery_repayment_period')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_home_loans', function (Blueprint $table) {
            $table->dropColumn(['recovery_interest', 'recovery_sentioned', 'recovery_repayment_type', 'recovery_repayment_period']);
        });
    }
};
