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
        Schema::table('erp_purchase_indents', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->change()->nullable();
            $table->string('requester_type', 50)->default('Department')->after('department_id');
            $table->unsignedBigInteger('user_id')->nullable()->after('requester_type');
        });
        Schema::table('erp_purchase_indents_history', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->change()->nullable();
            $table->string('requester_type', 50)->default('Department')->after('department_id');
            $table->unsignedBigInteger('user_id')->nullable()->after('requester_type');
        });
        Schema::table('erp_material_issue_header', function (Blueprint $table) {
            $table->dropColumn('department_code');
            $table->unsignedBigInteger('department_id')->change()->nullable();
            $table->string('requester_type', 50)->default('Department')->after('department_id');
            $table->unsignedBigInteger('user_id')->nullable()->after('requester_type');
        });
        Schema::table('erp_material_issue_header_history', function (Blueprint $table) {
            $table->dropColumn('department_code');
            $table->unsignedBigInteger('department_id')->change()->nullable();
            $table->string('requester_type', 50)->default('Department')->after('department_id');
            $table->unsignedBigInteger('user_id')->nullable()->after('requester_type');
        });
        Schema::table('erp_mi_items', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->after('pi_item_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->after('department_id');
        });
        Schema::table('erp_mi_items_history', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->after('pi_item_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->after('department_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_purchase_indents', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->change()->nullable(false);
            $table->dropColumn(['requester_type', 'user_id']);
        });
        Schema::table('erp_purchase_indents_history', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->change()->nullable(false);
            $table->dropColumn(['requester_type', 'user_id']);
        });
        Schema::table('erp_material_issue_header', function (Blueprint $table) {
            $table->string('department_code')->nullable();
            $table->unsignedBigInteger('department_id')->change()->nullable(false);
            $table->dropColumn(['requester_type', 'user_id']);
        });
        Schema::table('erp_material_issue_header_history', function (Blueprint $table) {
            $table->string('department_code')->nullable();
            $table->unsignedBigInteger('department_id')->change()->nullable(false);
            $table->dropColumn(['requester_type', 'user_id']);
        });
        Schema::table('erp_mi_items', function (Blueprint $table) {
            $table->dropColumn(['department_id', 'user_id']);
        });
        Schema::table('erp_mi_items_history', function (Blueprint $table) {
            $table->dropColumn(['department_id', 'user_id']);
        });
    }
};
