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
        Schema::table('erp_jo_products', function (Blueprint $table) {
            $table->double('ge_qty', 20, 6)->default(0.000000)->after('short_close_qty');
        });

        Schema::table('erp_gate_entry_headers', function (Blueprint $table) {
            $table->unsignedBigInteger('job_order_id')->nullable()->after('purchase_order_id');
            $table->string('reference_type', 299)->nullable()->after('job_order_id');
        });

        Schema::table('erp_gate_entry_headers_history', function (Blueprint $table) {
            $table->unsignedBigInteger('job_order_id')->nullable()->after('purchase_order_id');
            $table->string('reference_type', 299)->nullable()->after('job_order_id');
        });

        Schema::table('erp_gate_entry_details', function (Blueprint $table) {
            $table->unsignedBigInteger('job_order_item_id')->nullable()->after('purchase_order_item_id');
        });

        Schema::table('erp_gate_entry_details_history', function (Blueprint $table) {
            $table->unsignedBigInteger('job_order_item_id')->nullable()->after('purchase_order_item_id');
        });

        Schema::table('erp_mrn_headers', function (Blueprint $table) {
            $table->unsignedBigInteger('job_order_id')->nullable()->after('purchase_order_id');
            $table->string('reference_type', 299)->nullable()->after('job_order_id');
        });

        Schema::table('erp_mrn_header_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('job_order_id')->nullable()->after('purchase_order_id');
            $table->string('reference_type', 299)->nullable()->after('job_order_id');
        });

        Schema::table('erp_mrn_details', function (Blueprint $table) {
            $table->unsignedBigInteger('job_order_item_id')->nullable()->after('purchase_order_item_id');
        });

        Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('job_order_item_id')->nullable()->after('purchase_order_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_jo_products', function (Blueprint $table) {
            $table->dropColumn('ge_qty');
        });

        Schema::table('erp_gate_entry_headers', function (Blueprint $table) {
            $table->dropColumn(['job_order_id', 'reference_type']);
        });

        Schema::table('erp_gate_entry_headers_history', function (Blueprint $table) {
            $table->dropColumn(['job_order_id', 'reference_type']);
        });

        Schema::table('erp_gate_entry_details', function (Blueprint $table) {
            $table->dropColumn('job_order_item_id');
        });

        Schema::table('erp_gate_entry_details_history', function (Blueprint $table) {
            $table->dropColumn('job_order_item_id');
        });

        Schema::table('erp_mrn_headers', function (Blueprint $table) {
            $table->dropColumn(['job_order_id', 'reference_type']);
        });

        Schema::table('erp_mrn_header_histories', function (Blueprint $table) {
            $table->dropColumn(['job_order_id', 'reference_type']);
        });

        Schema::table('erp_mrn_details', function (Blueprint $table) {
            $table->dropColumn('job_order_item_id');
        });

        Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
            $table->dropColumn('job_order_item_id');
        });
    }
};
