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
             $table->date('last_apply_date')->nullable()->index()->after('hide_from_candidate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_recruitment_job', function (Blueprint $table) {
            $table->dropColumn('last_apply_date');
        });
    }
};
