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
        Schema::create('erp_loan_vehicle_scheme_costs', function (Blueprint $table) {
            $table->id();
            $table->integer('vehicle_id');
            $table->string('model')->nullable();
            $table->string('make')->nullable();
            $table->string('h_p')->nullable();
            $table->string('carry_capacity')->nullable();
            $table->string('classic_vessel')->nullable();
            $table->string('body_building')->nullable();
            $table->string('other_item')->nullable();
            $table->string('spares_tyres')->nullable();
            $table->string('insurance_taxes')->nullable();
            $table->string('pre_operative_exp')->nullable();
            $table->string('working_c_margin')->nullable();
            $table->string('total')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_loan_vehicle_scheme_costs');
    }
};
