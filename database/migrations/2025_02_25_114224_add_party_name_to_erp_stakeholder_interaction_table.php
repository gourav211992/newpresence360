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
        Schema::table('erp_stakeholder_interactions', function (Blueprint $table) {
            $table->string('party_name')->nullable();
        });
        Schema::table('erp_complaint_management', function (Blueprint $table) {
            $table->string('party_name')->nullable();
        });
        Schema::table('erp_feedback_processes', function (Blueprint $table) {
            $table->string('party_name')->nullable();
        });
        Schema::table('erp_public_outreach_and_communications', function (Blueprint $table) {
            $table->string('party_name')->nullable();
        });
        Schema::table('erp_engagement_trackings', function (Blueprint $table) {
            $table->string('party_name')->nullable();
        });
        Schema::table('erp_investor_relation_management', function (Blueprint $table) {
            $table->string('party_name')->nullable();
        });
        Schema::table('erp_gov_relation_management', function (Blueprint $table) {
            $table->string('party_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_stakeholder_interactions', function (Blueprint $table) {
            $table->dropColumn('party_name');
        });
        Schema::table('erp_complaint_management', function (Blueprint $table) {
            $table->dropColumn('party_name');
        });
        Schema::table('erp_feedback_processes', function (Blueprint $table) {
            $table->dropColumn('party_name');
        });
        Schema::table('erp_public_outreach_and_communications', function (Blueprint $table) {
            $table->dropColumn('party_name');
        });
        Schema::table('erp_engagement_trackings', function (Blueprint $table) {
            $table->dropColumn('party_name');
        });
        Schema::table('erp_investor_relation_management', function (Blueprint $table) {
            $table->dropColumn('party_name');
        });
        Schema::table('erp_gov_relation_management', function (Blueprint $table) {
            $table->dropColumn('party_name');
        });
    }
};
