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
        Schema::create('erp_po_mrn_mapping', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('po_id')->nullable();
            $table->unsignedBigInteger('po_item_id')->nullable();
            $table->unsignedBigInteger('mrn_header_id')->nullable();
            $table->unsignedBigInteger('mrn_detail_id')->nullable();
            $table->json('so_id')->nullable();
            $table->double('mrn_qty',18,6)->default(0.00);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
        });

        if (!Schema::hasColumn('erp_pi_po_mapping', 'grn_qty')) {
            Schema::table('erp_pi_po_mapping', function (Blueprint $table) {
                $table->double('grn_qty',18,6)->default(0.00)->after('po_qty');
            });
        }

        if (Schema::hasColumn('erp_gate_entry_details', 'so_item_id')) {
            Schema::table('erp_gate_entry_details', function (Blueprint $table) {
                $table->dropColumn('so_item_id');
            });
        }

        if (Schema::hasColumn('erp_gate_entry_details_history', 'so_item_id')) {
            Schema::table('erp_gate_entry_details_history', function (Blueprint $table) {
                $table->dropColumn('so_item_id');
            });
        }

        if (Schema::hasColumn('erp_mrn_details', 'so_item_id')) {
            Schema::table('erp_mrn_details', function (Blueprint $table) {
                $table->dropColumn('so_item_id');
            });
        }

        if (Schema::hasColumn('erp_mrn_detail_histories', 'so_item_id')) {
            Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
                $table->dropColumn('so_item_id');
            });
        }

        if (Schema::hasColumn('erp_purchase_return_details', 'so_item_id')) {
            Schema::table('erp_purchase_return_details', function (Blueprint $table) {
                $table->dropColumn('so_item_id');
            });
        }

        if (Schema::hasColumn('erp_purchase_return_details_history', 'so_item_id')) {
            Schema::table('erp_purchase_return_details_history', function (Blueprint $table) {
                $table->dropColumn('so_item_id');
            });
        }

        if (Schema::hasColumn('erp_pb_details', 'so_item_id')) {
            Schema::table('erp_pb_details', function (Blueprint $table) {
                $table->dropColumn('so_item_id');
            });
        }

        if (Schema::hasColumn('erp_pb_detail_histories', 'so_item_id')) {
            Schema::table('erp_pb_detail_histories', function (Blueprint $table) {
                $table->dropColumn('so_item_id');
            });
        }

        if (Schema::hasColumn('erp_expense_details', 'so_item_id')) {
            Schema::table('erp_expense_details', function (Blueprint $table) {
                $table->dropColumn('so_item_id');
            });
        }

        if (Schema::hasColumn('erp_expense_detail_histories', 'so_item_id')) {
            Schema::table('erp_expense_detail_histories', function (Blueprint $table) {
                $table->dropColumn('so_item_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('erp_pi_po_mapping', 'grn_qty')) {
            Schema::table('erp_pi_po_mapping', function (Blueprint $table) {
                $table->dropColumn('grn_qty');
            });
        }

        Schema::dropIfExists('erp_po_mrn_mapping');
    }
};
