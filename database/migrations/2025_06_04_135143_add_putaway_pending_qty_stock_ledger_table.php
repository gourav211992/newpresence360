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
        if (Schema::hasTable('stock_ledger') && !Schema::hasColumn('stock_ledger', 'putaway_pending_qty')) {
            Schema::table('stock_ledger', function (Blueprint $table) {
                $table->decimal('putaway_pending_qty', 15,2)->default(0.00)->after('hold_qty');
            });
        }

        if (Schema::hasTable('erp_mrn_details') && !Schema::hasColumn('erp_mrn_details', 'putaway_qty')) {
            Schema::table('erp_mrn_details', function (Blueprint $table) {
                $table->decimal('putaway_qty', 20,6)->default(0.00)->after('pr_rejected_qty');
            });
        }

        if (Schema::hasTable('erp_mrn_detail_histories') && !Schema::hasColumn('erp_mrn_detail_histories', 'putaway_qty')) {
            Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
                $table->decimal('putaway_qty', 20,6)->default(0.00)->after('pr_rejected_qty');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('erp_mrn_detail_histories') && Schema::hasColumn('erp_mrn_detail_histories', 'putaway_qty')) {
            Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
                $table->dropColumn('putaway_qty');
            });
        }

        if (Schema::hasTable('erp_mrn_details') && Schema::hasColumn('erp_mrn_details', 'putaway_qty')) {
            Schema::table('erp_mrn_details', function (Blueprint $table) {
                $table->dropColumn('putaway_qty');
            });
        }

        if (Schema::hasTable('stock_ledger') && Schema::hasColumn('stock_ledger', 'putaway_qty')) {
            Schema::table('stock_ledger', function (Blueprint $table) {
                $table->dropColumn('putaway_pending_qty');
            });
        }
    }
};
