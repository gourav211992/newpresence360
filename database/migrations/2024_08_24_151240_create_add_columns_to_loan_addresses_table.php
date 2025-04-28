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
        Schema::table('erp_loan_addresses', function (Blueprint $table) {
            $table->string('p_address1')->nullable();
            $table->string('p_address2')->nullable();
            $table->string('p_city')->nullable();
            $table->string('p_state')->nullable();
            $table->string('p_pin')->nullable();
            $table->string('p_resi_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_loan_addresses', function (Blueprint $table) {
            $table->dropColumn(['p_address1','p_address2','p_city','p_state','p_pin','p_resi_code']);
        });
    }
};
