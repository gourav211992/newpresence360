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
        Schema::table('erp_material_issue_header', function (Blueprint $table) {
            $table->unsignedBigInteger('station_id')->after('issue_type')->nullable();
        });
        Schema::table('erp_material_issue_header_history', function (Blueprint $table) {
            $table->unsignedBigInteger('station_id')->after('issue_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_material_issue_header', function (Blueprint $table) {
            $table->dropColumn(['station_id']);
        });
        Schema::table('erp_material_issue_header_history', function (Blueprint $table) {
            $table->dropColumn(['station_id']);
        });
    }
};
