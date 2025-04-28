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
        // Adding to mrn_header_histories table
        if (!Schema::hasColumn('erp_mrn_headers', 'attachment')) {
            Schema::table('erp_mrn_headers', function (Blueprint $table) {
                $table->json('attachment')->nullable();
            });
        }

        // Adding to erp_expense_headers table
        if (!Schema::hasColumn('erp_expense_headers', 'attachment')) {
            Schema::table('erp_expense_headers', function (Blueprint $table) {
                $table->json('attachment')->nullable();
            });
        }

        // Adding to erp_pb_headers table
        if (!Schema::hasColumn('erp_pb_headers', 'attachment')) {
            Schema::table('erp_pb_headers', function (Blueprint $table) {
                $table->json('attachment')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_mrn_headers', function (Blueprint $table) {
            $table->dropColumn('attachment');
        });

        Schema::table('erp_expense_headers', function (Blueprint $table) {
            $table->dropColumn('attachment');
        });

        Schema::table('erp_pb_headers', function (Blueprint $table) {
            $table->dropColumn('attachment');
        });
    }
};
