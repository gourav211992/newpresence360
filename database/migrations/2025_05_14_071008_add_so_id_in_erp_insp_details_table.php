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
        if (!Schema::hasColumn('erp_insp_details', 'so_id')) {
            Schema::table('erp_insp_details', function (Blueprint $table) {
                $table->unsignedBigInteger('so_id')->after('item_name')->nullable();
            });
        }

        if (!Schema::hasColumn('erp_insp_details_history', 'so_id')) {
            Schema::table('erp_insp_details_history', function (Blueprint $table) {
                $table->unsignedBigInteger('so_id')->after('item_name')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('erp_insp_details_history', 'so_id')) {
            Schema::table('erp_insp_details_history', function (Blueprint $table) {
                $table->dropColumn('so_id');
            });
        }

        if (Schema::hasColumn('erp_insp_details', 'so_id')) {
            Schema::table('erp_insp_details', function (Blueprint $table) {
                $table->dropColumn('so_id');
            });
        }
    }
};
