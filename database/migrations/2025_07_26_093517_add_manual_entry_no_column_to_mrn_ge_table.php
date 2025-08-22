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
        Schema::table('erp_mrn_headers', function (Blueprint $table) {
            $table->string('manual_entry_no')->nullable()->after('doc_no');
        });

        Schema::table('erp_mrn_header_histories', function (Blueprint $table) {
            $table->string('manual_entry_no')->nullable()->after('doc_no');
        });

        Schema::table('erp_gate_entry_headers', function (Blueprint $table) {
            $table->string('manual_entry_no')->nullable()->after('doc_no');
        });

        Schema::table('erp_gate_entry_headers_history', function (Blueprint $table) {
            $table->string('manual_entry_no')->nullable()->after('doc_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_mrn_headers', function (Blueprint $table) {
            $table->dropColumn('manual_entry_no');
        });

        Schema::table('erp_mrn_header_histories', function (Blueprint $table) {
            $table->dropColumn('manual_entry_no');
        });

        Schema::table('erp_gate_entry_headers', function (Blueprint $table) {
            $table->dropColumn('manual_entry_no');
        });

        Schema::table('erp_gate_entry_headers_history', function (Blueprint $table) {
            $table->dropColumn('manual_entry_no');
        });
    }
};
