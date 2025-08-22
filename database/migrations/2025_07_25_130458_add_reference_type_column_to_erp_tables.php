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
        Schema::table('erp_expense_headers', function (Blueprint $table) {
            $table->string('reference_type')->nullable()->after('reference_number');
        });

        Schema::table('erp_expense_header_histories', function (Blueprint $table) {
            $table->string('reference_type')->nullable()->after('reference_number');
        });

        Schema::table('erp_pb_headers', function (Blueprint $table) {
            $table->string('reference_type')->nullable()->after('reference_number');
        });

        Schema::table('erp_pb_header_histories', function (Blueprint $table) {
            $table->string('reference_type')->nullable()->after('reference_number');
        });

        Schema::table('erp_purchase_return_headers', function (Blueprint $table) {
            $table->string('reference_type')->nullable()->after('reference_number');
        });

        Schema::table('erp_purchase_return_headers_history', function (Blueprint $table) {
            $table->string('reference_type')->nullable()->after('reference_number');
        });

        Schema::table('erp_insp_headers', function (Blueprint $table) {
            $table->string('reference_type')->nullable()->after('reference_number');
        });

        Schema::table('erp_insp_headers_history', function (Blueprint $table) {
            $table->string('reference_type')->nullable()->after('reference_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_expense_headers', function (Blueprint $table) {
            $table->dropColumn('reference_type');
        });

        Schema::table('erp_expense_header_histories', function (Blueprint $table) {
            $table->dropColumn('reference_type');
        });

        Schema::table('erp_pb_headers', function (Blueprint $table) {
            $table->dropColumn('reference_type');
        });

        Schema::table('erp_pb_header_histories', function (Blueprint $table) {
            $table->dropColumn('reference_type');
        });

        Schema::table('erp_purchase_return_headers', function (Blueprint $table) {
            $table->dropColumn('reference_type');
        });

        Schema::table('erp_purchase_return_headers_history', function (Blueprint $table) {
            $table->dropColumn('reference_type');
        });

        Schema::table('erp_insp_headers', function (Blueprint $table) {
            $table->dropColumn('reference_type');
        });

        Schema::table('erp_insp_headers_history', function (Blueprint $table) {
            $table->dropColumn('reference_type');
        });
    }
};
