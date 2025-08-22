<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasColumn('erp_vendor_asn_items', 'po_id')) {
            Schema::table('erp_vendor_asn_items', function (Blueprint $table) {
                $table->unsignedBigInteger('po_id')->nullable()->after('vendor_asn_id');
            });
        }

        if (!Schema::hasColumn('erp_vendor_asn_items', 'jo_id')) {
            Schema::table('erp_vendor_asn_items', function (Blueprint $table) {
                $table->unsignedBigInteger('jo_id')->nullable()->after('po_item_id');
            });
        }

        Schema::table('erp_gate_entry_details', function (Blueprint $table) {
            $table->unsignedBigInteger('po_id')->nullable()->after('purchase_order_item_id');
            $table->unsignedBigInteger('jo_id')->nullable()->after('job_order_item_id');
        });

        Schema::table('erp_gate_entry_details_history', function (Blueprint $table) {
            $table->unsignedBigInteger('po_id')->nullable()->after('purchase_order_item_id');
            $table->unsignedBigInteger('jo_id')->nullable()->after('job_order_item_id');
        });

        Schema::table('erp_gate_entry_headers', function (Blueprint $table) {
            $table->json('po_numbers')->nullable()->after('purchase_order_id');
            $table->json('jo_numbers')->nullable()->after('job_order_id');
        });

        Schema::table('erp_gate_entry_headers_history', function (Blueprint $table) {
            $table->json('po_numbers')->nullable()->after('purchase_order_id');
            $table->json('jo_numbers')->nullable()->after('job_order_id');
        });

        Schema::table('erp_mrn_details', function (Blueprint $table) {
            $table->unsignedBigInteger('po_id')->nullable()->after('purchase_order_item_id');
            $table->unsignedBigInteger('jo_id')->nullable()->after('job_order_item_id');
            $table->unsignedBigInteger('ge_id')->nullable()->after('gate_entry_detail_id');
        });

        Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('po_id')->nullable()->after('purchase_order_item_id');
            $table->unsignedBigInteger('jo_id')->nullable()->after('job_order_item_id');
            $table->unsignedBigInteger('ge_id')->nullable()->after('gate_entry_detail_id');
        });

        Schema::table('erp_mrn_headers', function (Blueprint $table) {
            $table->json('po_numbers')->nullable()->after('purchase_order_id');
            $table->json('jo_numbers')->nullable()->after('job_order_id');
            $table->json('ge_numbers')->nullable()->after('jo_numbers');
        });

        Schema::table('erp_mrn_header_histories', function (Blueprint $table) {
            $table->json('po_numbers')->nullable()->after('purchase_order_id');
            $table->json('jo_numbers')->nullable()->after('job_order_id');
            $table->json('ge_numbers')->nullable()->after('jo_numbers');
        });
    }

    public function down(): void {
        Schema::table('erp_mrn_header_histories', function (Blueprint $table) {
            $table->dropColumn(['po_numbers', 'jo_numbers', 'ge_numbers']);
        });

        Schema::table('erp_mrn_headers', function (Blueprint $table) {
            $table->dropColumn(['po_numbers', 'jo_numbers', 'ge_numbers']);
        });

        Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
            $table->dropColumn(['po_id', 'jo_id', 'ge_id']);
        });

        Schema::table('erp_mrn_details', function (Blueprint $table) {
            $table->dropColumn(['po_id', 'jo_id', 'ge_id']);
        });

        Schema::table('erp_gate_entry_headers_history', function (Blueprint $table) {
            $table->dropColumn(['po_numbers', 'jo_numbers']);
        });

        Schema::table('erp_gate_entry_headers', function (Blueprint $table) {
            $table->dropColumn(['po_numbers', 'jo_numbers']);
        });

        Schema::table('erp_gate_entry_details_history', function (Blueprint $table) {
            $table->dropColumn(['po_id', 'jo_id']);
        });

        Schema::table('erp_gate_entry_details', function (Blueprint $table) {
            $table->dropColumn(['po_id', 'jo_id']);
        });

        Schema::table('erp_vendor_asn_items', function (Blueprint $table) {
            $table->dropColumn(['po_id', 'jo_id']);
        });
    }
};