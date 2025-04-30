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
        Schema::create('erp_employee_stores', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('employee_id')->index();
            $table->foreign('employee_id')->references('id')->on('employees');
            $table->unsignedBigInteger('location_id')->index();
            $table->foreign('location_id')->references('id')->on('erp_stores');
            $table->timestamps();
        });

        Schema::create('erp_employee_sub_stores', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('employee_id')->index();
            $table->foreign('employee_id')->references('id')->on('employees');
            $table->unsignedBigInteger('sub_location_id')->index();
            $table->foreign('sub_location_id')->references('id')->on('erp_sub_stores');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_employee_stores');
        Schema::dropIfExists('erp_employee_sub_stores');
    }
};
