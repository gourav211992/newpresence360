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
        Schema::create('erp_loan_guar_applicants', function (Blueprint $table) {
            $table->id();
            $table->integer('home_loan_id');
            $table->string('name')->nullable();
            $table->string('fm_name')->nullable();
            $table->string('encumbered')->nullable();
            $table->string('land_plot')->nullable();
            $table->string('agriculture_land')->nullable();
            $table->string('h_godowns')->nullable();
            $table->string('other')->nullable();
            $table->string('est_val')->nullable();
            $table->longText('oth_liability')->nullable();
            $table->longText('bank_name')->nullable();
            $table->longText('purpose')->nullable();
            $table->longText('loan_amount')->nullable();
            $table->longText('overdue')->nullable();
            $table->longText('personal_guarantee')->nullable();
            $table->longText('person_behalf')->nullable();
            $table->longText('commitment_amnt')->nullable();
            $table->string('guarntr_image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_loan_guar_applicants');
    }
};
