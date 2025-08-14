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
        Schema::table('erp_term_loan_net_worths', function (Blueprint $table) {
            $table->text('unit_address1')->nullable()->change();
            $table->text('unit_address2')->nullable()->change();
            $table->text('resi_address1')->nullable()->change();
            $table->text('resi_address2')->nullable()->change();
            $table->text('bank_address')->nullable()->change();
            $table->text('club_address')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_term_loan_net_worths', function (Blueprint $table) {
            $table->string('unit_address1')->nullable()->change();
            $table->string('unit_address2')->nullable()->change();
            $table->string('resi_address1')->nullable()->change();
            $table->string('resi_address2')->nullable()->change();
            $table->string('bank_address')->nullable()->change();
            $table->string('club_address')->nullable()->change();
        });
    }
};
