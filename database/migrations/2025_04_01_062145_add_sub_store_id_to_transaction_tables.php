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
        //DN
        Schema::table('erp_sale_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('sub_store_id')->after('store_id')->nullable();
        });
        Schema::table('erp_sale_invoices_history', function (Blueprint $table) {
            $table->unsignedBigInteger('sub_store_id')->after('store_id')->nullable();
        });
        Schema::table('erp_invoice_items', function (Blueprint $table) {
            $table->unsignedBigInteger('sub_store_id')->after('store_id')->nullable();
        });
        Schema::table('erp_invoice_items_history', function (Blueprint $table) {
            $table->unsignedBigInteger('sub_store_id')->after('store_id')->nullable();
        });
        //MI
        Schema::table('erp_material_issue_header', function (Blueprint $table) {
            $table->unsignedBigInteger('from_sub_store_id')->after('from_store_id')->nullable();
            $table->unsignedBigInteger('to_sub_store_id')->after('to_store_id')->nullable();
        });
        Schema::table('erp_material_issue_header_history', function (Blueprint $table) {
            $table->unsignedBigInteger('from_sub_store_id')->after('from_store_id')->nullable();
            $table->unsignedBigInteger('to_sub_store_id')->after('to_store_id')->nullable();
        });
        Schema::table('erp_mi_items', function (Blueprint $table) {
            $table->unsignedBigInteger('from_sub_store_id')->after('from_store_id')->nullable();
            $table->unsignedBigInteger('to_sub_store_id')->after('to_store_id')->nullable();
        });
        Schema::table('erp_mi_items_history', function (Blueprint $table) {
            $table->unsignedBigInteger('from_sub_store_id')->after('from_store_id')->nullable();
            $table->unsignedBigInteger('to_sub_store_id')->after('to_store_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_sale_invoices', function (Blueprint $table) {
            $table->dropColumn(['sub_store_id']);
        });
        Schema::table('erp_sale_invoices_history', function (Blueprint $table) {
            $table->dropColumn('sub_store_id');
        });
        Schema::table('erp_invoice_items', function (Blueprint $table) {
            $table->dropColumn('sub_store_id');
        });
        Schema::table('erp_invoice_items_history', function (Blueprint $table) {
            $table->dropColumn('sub_store_id');
        });
        Schema::table('erp_material_issue_header', function (Blueprint $table) {
            $table->dropColumn(['from_sub_store_id']);
            $table->dropColumn(['to_sub_store_id']);
        });
        Schema::table('erp_material_issue_header_history', function (Blueprint $table) {
            $table->dropColumn(['from_sub_store_id']);
            $table->dropColumn(['to_sub_store_id']);
        });
        Schema::table('erp_mi_items', function (Blueprint $table) {
            $table->dropColumn(['from_sub_store_id']);
            $table->dropColumn(['to_sub_store_id']);
        });
        Schema::table('erp_mi_items_history', function (Blueprint $table) {
            $table->dropColumn(['from_sub_store_id']);
            $table->dropColumn(['to_sub_store_id']);
        });
    }
};
