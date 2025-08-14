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
        Schema::table('erp_home_loans', function (Blueprint $table) {
            $table->string('ass_recom_amnt')->nullable();
            $table->string('ass_cibil')->nullable();
            $table->string('ass_doc')->nullable();
            $table->string('ass_remarks')->nullable();
            $table->string('disbursal_amnt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_home_loans', function (Blueprint $table) {
            $table->dropColumn(['ass_recom_amnt', 'ass_cibil', 'ass_doc', 'ass_remarks', 'disbursal_amnt']);
        });
    }
};
