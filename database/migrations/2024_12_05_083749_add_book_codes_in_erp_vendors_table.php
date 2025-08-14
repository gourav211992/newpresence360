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
        Schema::table('erp_vendors', function (Blueprint $table) {
            $table->json('book_codes')->nullable()->after('stop_payment');
        });

        Schema::table('erp_mrn_headers', function (Blueprint $table) {
            $table->string('bill_to_follow', 100)->default('no')->nullable()->after('final_remarks');
        });

        Schema::table('erp_mrn_header_histories', function (Blueprint $table) {
            $table->string('bill_to_follow', 100)->default('no')->nullable()->after('final_remarks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_mrn_header_histories', function (Blueprint $table) {
            $table->dropColumn('bill_to_follow');
        });

        Schema::table('erp_mrn_headers', function (Blueprint $table) {
            $table->dropColumn('bill_to_follow');
        });

        Schema::table('erp_vendors', function (Blueprint $table) {
            $table->dropColumn('book_codes');
        });
    }
};
