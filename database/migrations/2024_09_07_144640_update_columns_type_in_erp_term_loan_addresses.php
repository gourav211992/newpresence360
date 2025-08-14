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
        Schema::table('erp_term_loan_addresses', function (Blueprint $table) {
            $table->text('addr1')->nullable()->change();
            $table->text('addr2')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_term_loan_addresses', function (Blueprint $table) {
            $table->string('addr1')->nullable()->change();
            $table->string('addr2')->nullable()->change();
        });
    }
};
