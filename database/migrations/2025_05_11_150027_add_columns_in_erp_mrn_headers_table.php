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
            $table->tinyInteger('is_inspection_completion')->default(0)->after('is_warehouse_required');
        });

        Schema::table('erp_mrn_header_histories', function (Blueprint $table) {
            $table->tinyInteger('is_inspection_completion')->default(0)->after('is_warehouse_required');
        });

        Schema::table('erp_mrn_details', function (Blueprint $table) {
            $table->decimal('inspection_qty', 20,6)->default(0.000000)->after('pr_qty');
        });

        Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
            $table->decimal('inspection_qty', 20,6)->default(0.000000)->after('pr_qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
            $table->dropColumn(['inspection_qty']);
        });
        Schema::table('erp_mrn_details', function (Blueprint $table) {
            $table->dropColumn(['inspection_qty']);
        });
        Schema::table('erp_mrn_header_histories', function (Blueprint $table) {
            $table->dropColumn(['is_inspection_completion']);
        });
        Schema::table('erp_mrn_headers', function (Blueprint $table) {
            $table->dropColumn(['is_inspection_completion']);
        });
    }
};
