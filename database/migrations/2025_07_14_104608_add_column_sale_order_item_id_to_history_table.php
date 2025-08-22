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
        if (!Schema::hasColumn('erp_gate_entry_details_history', 'sale_order_item_id')) {
            Schema::table('erp_gate_entry_details_history', function (Blueprint $table) {
                $table->unsignedBigInteger('sale_order_item_id')->nullable()->after('so_id');
            });
        }

        if (!Schema::hasColumn('erp_gate_entry_headers_history', 'sale_order_id')) {
            Schema::table('erp_gate_entry_headers_history', function (Blueprint $table) {
                $table->unsignedBigInteger('sale_order_id')->nullable()->after('job_order_id');
            });
        }

        if (!Schema::hasColumn('erp_mrn_detail_histories', 'sale_order_item_id')) {
            Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
                $table->unsignedBigInteger('sale_order_item_id')->nullable()->after('so_id');
            });
        }

        if (!Schema::hasColumn('erp_mrn_header_histories', 'sale_order_id')) {
            Schema::table('erp_mrn_header_histories', function (Blueprint $table) {
                $table->unsignedBigInteger('sale_order_id')->nullable()->after('job_order_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_gate_entry_details_history', function (Blueprint $table) {
            $table->dropColumn('sale_order_item_id');
        });

        Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
            $table->dropColumn('sale_order_item_id');
        });

        Schema::table('erp_gate_entry_headers_history', function (Blueprint $table) {
            $table->dropColumn('sale_order_id');
        });

        Schema::table('erp_mrn_header_histories', function (Blueprint $table) {
            $table->dropColumn('sale_order_id');
        });
    }
};
