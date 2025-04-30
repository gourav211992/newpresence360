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
        if (!Schema::hasColumn('erp_gate_entry_details', 'so_id')) {
            Schema::table('erp_gate_entry_details', function (Blueprint $table) {
                $table->unsignedBigInteger('so_id')->after('item_name')->nullable();
            });
        }

        if (!Schema::hasColumn('erp_gate_entry_details_history', 'so_id')) {
            Schema::table('erp_gate_entry_details_history', function (Blueprint $table) {
                $table->unsignedBigInteger('so_id')->after('item_name')->nullable();
            });
        }

        if (!Schema::hasColumn('erp_mrn_details', 'so_id')) {
            Schema::table('erp_mrn_details', function (Blueprint $table) {
                $table->unsignedBigInteger('so_id')->after('item_name')->nullable();
            });
        }

        if (!Schema::hasColumn('erp_mrn_detail_histories', 'so_id')) {
            Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
                $table->unsignedBigInteger('so_id')->after('item_name')->nullable();
            });
        }

        if (!Schema::hasColumn('erp_purchase_return_details', 'so_id')) {
            Schema::table('erp_purchase_return_details', function (Blueprint $table) {
                $table->unsignedBigInteger('so_id')->after('item_name')->nullable();
            });
        }

        if (!Schema::hasColumn('erp_purchase_return_details_history', 'so_id')) {
            Schema::table('erp_purchase_return_details_history', function (Blueprint $table) {
                $table->unsignedBigInteger('so_id')->after('item_name')->nullable();
            });
        }

        if (!Schema::hasColumn('erp_pb_details', 'so_id')) {
            Schema::table('erp_pb_details', function (Blueprint $table) {
                $table->unsignedBigInteger('so_id')->after('item_name')->nullable();
            });
        }

        if (!Schema::hasColumn('erp_pb_detail_histories', 'so_id')) {
            Schema::table('erp_pb_detail_histories', function (Blueprint $table) {
                $table->unsignedBigInteger('so_id')->after('item_name')->nullable();
            });
        }

        if (!Schema::hasColumn('erp_expense_details', 'so_id')) {
            Schema::table('erp_expense_details', function (Blueprint $table) {
                $table->unsignedBigInteger('so_id')->after('item_name')->nullable();
            });
        }

        if (!Schema::hasColumn('erp_expense_detail_histories', 'so_id')) {
            Schema::table('erp_expense_detail_histories', function (Blueprint $table) {
                $table->unsignedBigInteger('so_id')->after('item_name')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('erp_expense_detail_histories', 'so_id')) {
            Schema::table('erp_expense_detail_histories', function (Blueprint $table) {
                $table->dropColumn('so_id');
            });
        }

        if (Schema::hasColumn('erp_expense_details', 'so_id')) {
            Schema::table('erp_expense_details', function (Blueprint $table) {
                $table->dropColumn('so_id');
            });
        }

        if (Schema::hasColumn('erp_pb_detail_histories', 'so_id')) {
            Schema::table('erp_pb_detail_histories', function (Blueprint $table) {
                $table->dropColumn('so_id');
            });
        }

        if (Schema::hasColumn('erp_pb_details', 'so_id')) {
            Schema::table('erp_pb_details', function (Blueprint $table) {
                $table->dropColumn('so_id');
            });
        }

        if (Schema::hasColumn('erp_purchase_return_details_history', 'so_id')) {
            Schema::table('erp_purchase_return_details_history', function (Blueprint $table) {
                $table->dropColumn('so_id');
            });
        }

        if (Schema::hasColumn('erp_purchase_return_details', 'so_id')) {
            Schema::table('erp_purchase_return_details', function (Blueprint $table) {
                $table->dropColumn('so_id');
            });
        }

        if (Schema::hasColumn('erp_mrn_detail_histories', 'so_id')) {
            Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
                $table->dropColumn('so_id');
            });
        }

        if (Schema::hasColumn('erp_mrn_details', 'so_id')) {
            Schema::table('erp_mrn_details', function (Blueprint $table) {
                $table->dropColumn('so_id');
            });
        }

        if (Schema::hasColumn('erp_gate_entry_details_history', 'so_id')) {
            Schema::table('erp_gate_entry_details_history', function (Blueprint $table) {
                $table->dropColumn('so_id');
            });
        }

        if (Schema::hasColumn('erp_gate_entry_details', 'so_id')) {
            Schema::table('erp_gate_entry_details', function (Blueprint $table) {
                $table->dropColumn('so_id');
            });
        }
    }
};
