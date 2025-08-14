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
        Schema::create('erp_loan_addresses', function (Blueprint $table) {
            $table->id();
            $table->integer('home_loan_id');
            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->integer('pin_code')->nullable();
            $table->integer('years_current_addr')->nullable();
            $table->string('residence_phn')->nullable();
            $table->string('office_phn')->nullable();
            $table->string('fax_num')->nullable();
            $table->integer('same_as')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_loan_addresses');
    }
};
