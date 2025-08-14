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
        Schema::table('erp_loan_guarantor_co_applicants', function (Blueprint $table) {
            $table->string('image_co')->nullable()->after('commitment_amnt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_loan_guarantor_co_applicants', function (Blueprint $table) {
            //
        });
    }
};
