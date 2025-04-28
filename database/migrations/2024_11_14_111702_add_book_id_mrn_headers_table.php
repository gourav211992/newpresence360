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
            $table->unsignedBigInteger('book_id')->after('series_id')->nullable();
        });

        Schema::table('erp_mrn_header_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('book_id')->after('series_id')->nullable();
        });

        Schema::table('erp_pb_headers', function (Blueprint $table) {
            $table->unsignedBigInteger('book_id')->after('series_id')->nullable();
        });

        Schema::table('erp_pb_header_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('book_id')->after('series_id')->nullable();
        });

        Schema::table('erp_expense_headers', function (Blueprint $table) {
            $table->unsignedBigInteger('book_id')->after('series_id')->nullable();
        });

        Schema::table('erp_expense_header_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('book_id')->after('series_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_expense_header_histories', function (Blueprint $table) {
            $table->dropColumn('book_id');
        });

        Schema::table('erp_expense_headers', function (Blueprint $table) {
            $table->dropColumn('book_id');
        });

        Schema::table('erp_pb_header_histories', function (Blueprint $table) {
            $table->dropColumn('book_id');
        });

        Schema::table('erp_pb_headers', function (Blueprint $table) {
            $table->dropColumn('book_id');
        });

        Schema::table('erp_mrn_header_histories', function (Blueprint $table) {
            $table->dropColumn('book_id');
        });

        Schema::table('erp_mrn_headers', function (Blueprint $table) {
            $table->dropColumn('book_id');
        });
    }
};
