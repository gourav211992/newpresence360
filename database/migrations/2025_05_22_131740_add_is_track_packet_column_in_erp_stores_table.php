<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('erp_stores', function (Blueprint $table) {
            $table->tinyInteger('is_packet_tracking')->default(0)->after('contact_email');
        });

        // Update existing records where value is 0
        DB::table('erp_mrn_headers')->where('is_inspection_completion', 0)->update(['is_inspection_completion' => 1]);

        // Change default value to 1
        Schema::table('erp_mrn_headers', function (Blueprint $table) {
            $table->tinyInteger('is_inspection_completion')->default(1)->change();
        });

        // Update existing records where value is 0
        DB::table('erp_mrn_header_histories')->where('is_inspection_completion', 0)->update(['is_inspection_completion' => 1]);

        // Change default value to 1
        Schema::table('erp_mrn_header_histories', function (Blueprint $table) {
            $table->tinyInteger('is_inspection_completion')->default(1)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert default back to 0
        Schema::table('erp_mrn_header_histories', function (Blueprint $table) {
            $table->tinyInteger('is_inspection_completion')->default(0)->change();
        });

        // Revert default back to 0
        Schema::table('erp_mrn_headers', function (Blueprint $table) {
            $table->tinyInteger('is_inspection_completion')->default(0)->change();
        });

        Schema::table('erp_stores', function (Blueprint $table) {
            $table->dropColumn('is_packet_tracking');
        });
    }
};
