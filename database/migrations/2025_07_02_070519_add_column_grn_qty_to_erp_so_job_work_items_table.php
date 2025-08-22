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
        if (!Schema::hasColumn('erp_so_job_work_items', 'grn_qty')) {
            Schema::table('erp_so_job_work_items', function (Blueprint $table) {
            $table->double('grn_qty', 20, 6) -> default(0.00) -> after('qty');
        });
        }

        if (!Schema::hasColumn('erp_gate_entry_details', 'sale_order_item_id')) {
            Schema::table('erp_gate_entry_details', function (Blueprint $table) {
                $table->unsignedBigInteger('sale_order_item_id')->nullable()->after('so_id');
            });
        }

        if (!Schema::hasColumn('erp_gate_entry_headers', 'sale_order_id')) {
            Schema::table('erp_gate_entry_headers', function (Blueprint $table) {
                $table->unsignedBigInteger('sale_order_id')->nullable()->after('job_order_id');
            });
        }

        if (!Schema::hasColumn('erp_mrn_details', 'sale_order_item_id')) {
            Schema::table('erp_mrn_details', function (Blueprint $table) {
                $table->unsignedBigInteger('sale_order_item_id')->nullable()->after('so_id');
            });
        }

        if (!Schema::hasColumn('erp_mrn_headers', 'sale_order_id')) {
            Schema::table('erp_mrn_headers', function (Blueprint $table) {
                $table->unsignedBigInteger('sale_order_id')->nullable()->after('job_order_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_so_job_work_items', function (Blueprint $table) {
            $table->dropColumn('grn_qty');
        });

        Schema::table('erp_gate_entry_details', function (Blueprint $table) {
            $table->dropColumn('sale_order_item_id');
        });

        Schema::table('erp_mrn_details', function (Blueprint $table) {
            $table->dropColumn('sale_order_item_id');
        });

        Schema::table('erp_gate_entry_headers', function (Blueprint $table) {
            $table->dropColumn('sale_order_id');
        });

        Schema::table('erp_mrn_headers', function (Blueprint $table) {
            $table->dropColumn('sale_order_id');
        });
    }
};
