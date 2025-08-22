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
        Schema::table('erp_recruitment_job', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->index()->after('organization_id');
            $table->unsignedBigInteger('group_id')->nullable()->index()->after('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_recruitment_job', function (Blueprint $table) {
            $table->dropColumn('company_id');
            $table->dropColumn('group_id');
        });
    }
};
