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
        Schema::create('erp_home_loans', function (Blueprint $table) {
            $table->id();
            $table->integer('loan_amount');
            $table->string('scheme_for');
            $table->string('name');
            $table->string('gender')->nullable();
            $table->string('cast')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('father_mother_name')->nullable();
            $table->string('gir_no');
            $table->date('dob')->nullable();
            $table->integer('age')->nullable();
            $table->string('email')->nullable();
            $table->string('mobile')->nullable();
            $table->string('spouse_name')->nullable();
            $table->integer('no_of_depends');
            $table->integer('no_of_children')->nullable();
            $table->string('earning_member');
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_home_loans');
    }
};
