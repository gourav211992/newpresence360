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
        Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('erp_mrn_detail_histories', 'pr_qty')) {
                $table->decimal('pr_qty', 15, 2)->default(0.00)->after('purchase_bill_qty');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
            if (Schema::hasColumn('erp_mrn_detail_histories', 'pr_qty')) {
                $table->dropColumn('pr_qty');
            }
        });
    }
};
