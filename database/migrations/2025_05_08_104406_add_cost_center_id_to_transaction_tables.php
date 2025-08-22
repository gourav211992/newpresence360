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
        //MRN
        Schema::table('erp_mrn_headers', function (Blueprint $table) {
            $table->unsignedBigInteger('cost_center_id')->nullable()->after('document_number');
        });
        Schema::table('erp_mrn_header_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('cost_center_id')->nullable()->after('document_number');
        });
        //PB
        Schema::table('erp_pb_headers', function (Blueprint $table) {
            $table->unsignedBigInteger('cost_center_id')->nullable()->after('document_number');
        });
        Schema::table('erp_pb_header_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('cost_center_id')->nullable()->after('document_number');
        });
        //PR
        Schema::table('erp_purchase_return_headers', function (Blueprint $table) {
            $table->unsignedBigInteger('cost_center_id')->nullable()->after('document_number');
        });
        Schema::table('erp_purchase_return_headers_history', function (Blueprint $table) {
            $table->unsignedBigInteger('cost_center_id')->nullable()->after('document_number');
        });
        //SI
        Schema::table('erp_sale_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('cost_center_id')->nullable()->after('document_number');
        });
        Schema::table('erp_sale_invoices_history', function (Blueprint $table) {
            $table->unsignedBigInteger('cost_center_id')->nullable()->after('document_number');
        });
        //SR
        Schema::table('erp_sale_returns', function (Blueprint $table) {
            $table->unsignedBigInteger('cost_center_id')->nullable()->after('document_number');
        });
        Schema::table('erp_sale_return_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('cost_center_id')->nullable()->after('document_number');
        });
        //MO
        Schema::table('erp_mfg_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('cost_center_id')->nullable()->after('document_number');
        });
        Schema::table('erp_mfg_orders_history', function (Blueprint $table) {
            $table->unsignedBigInteger('cost_center_id')->nullable()->after('document_number');
        });
        //EXP
        Schema::table('erp_expense_headers', function (Blueprint $table) {
            $table->unsignedBigInteger('cost_center_id')->nullable()->after('document_number');
        });
        Schema::table('erp_expense_header_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('cost_center_id')->nullable()->after('document_number');
        });
        //PSLIP
        Schema::table('erp_production_slips', function (Blueprint $table) {
            $table->unsignedBigInteger('cost_center_id')->nullable()->after('document_number');
        });
        Schema::table('erp_production_slips_history', function (Blueprint $table) {
            $table->unsignedBigInteger('cost_center_id')->nullable()->after('document_number');
        });
        //PSV
        Schema::table('erp_psv_headers', function (Blueprint $table) {
            $table->unsignedBigInteger('cost_center_id')->nullable()->after('document_number');
        });
        //Add to history
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //MRN
        Schema::table('erp_mrn_headers', function (Blueprint $table) {
            $table->dropColumn(['cost_center_id']);
        });
        Schema::table('erp_mrn_header_histories', function (Blueprint $table) {
            $table->dropColumn(['cost_center_id']);
        });
        //PB
        Schema::table('erp_pb_headers', function (Blueprint $table) {
            $table->dropColumn(['cost_center_id']);
        });
        Schema::table('erp_pb_header_histories', function (Blueprint $table) {
            $table->dropColumn(['cost_center_id']);
        });
        //PR
        Schema::table('erp_purchase_return_headers', function (Blueprint $table) {
            $table->dropColumn(['cost_center_id']);
        });
        Schema::table('erp_purchase_return_headers_history', function (Blueprint $table) {
            $table->dropColumn(['cost_center_id']);
        });
        //SI
        Schema::table('erp_sale_invoices', function (Blueprint $table) {
            $table->dropColumn(['cost_center_id']);
        });
        Schema::table('erp_sale_invoices_history', function (Blueprint $table) {
            $table->dropColumn(['cost_center_id']);
        });
        //SR
        Schema::table('erp_sale_returns', function (Blueprint $table) {
            $table->dropColumn(['cost_center_id']);
        });
        Schema::table('erp_sale_return_histories', function (Blueprint $table) {
            $table->dropColumn(['cost_center_id']);
        });
        //MO
        Schema::table('erp_mfg_orders', function (Blueprint $table) {
            $table->dropColumn(['cost_center_id']);
        });
        Schema::table('erp_mfg_orders_history', function (Blueprint $table) {
            $table->dropColumn(['cost_center_id']);
        });
        //EXP
        Schema::table('erp_expense_headers', function (Blueprint $table) {
            $table->dropColumn(['cost_center_id']);
        });
        Schema::table('erp_expense_header_histories', function (Blueprint $table) {
            $table->dropColumn(['cost_center_id']);
        });
        //PSLIP
        Schema::table('erp_production_slips', function (Blueprint $table) {
            $table->dropColumn(['cost_center_id']);
        });
        Schema::table('erp_production_slips_history', function (Blueprint $table) {
            $table->dropColumn(['cost_center_id']);
        });
        //PSV
        Schema::table('erp_psv_headers', function (Blueprint $table) {
            $table->dropColumn(['cost_center_id']);
        });
        //Add to history
    }
};
