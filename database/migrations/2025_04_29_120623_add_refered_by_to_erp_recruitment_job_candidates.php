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
        Schema::table('erp_recruitment_job_candidates', function (Blueprint $table) {
            $table->unsignedBigInteger('refered_by')->nullable()->index()->after('resume_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_recruitment_job_candidates', function (Blueprint $table) {
            $table->dropColumn('refered_by');
        });
    }
};
