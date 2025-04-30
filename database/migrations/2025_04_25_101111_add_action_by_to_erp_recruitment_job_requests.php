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
        Schema::table('erp_recruitment_job_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('action_by')->nullable()->index()->after('created_by_type');
            $table->string('action_by_type')->default('employee')->index()->after('action_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_recruitment_job_requests', function (Blueprint $table) {
            $table->dropColumn('action_by');
            $table->dropColumn('action_by_type');
        });
    }
};
