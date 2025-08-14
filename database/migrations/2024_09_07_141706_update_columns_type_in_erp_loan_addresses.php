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
            $table->text('address1')->nullable()->change();
            $table->text('address2')->nullable()->change();
            $table->text('p_address1')->nullable()->change();
            $table->text('p_address2')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_loan_addresses', function (Blueprint $table) {
            $table->string('address1')->nullable()->change();
            $table->string('address2')->nullable()->change();
            $table->string('p_address1')->nullable()->change();
            $table->string('p_address2')->nullable()->change();
        });
    }
};
