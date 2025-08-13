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
        //
        if (Schema::hasColumn('erp_sr_item_lot_details', 'original_receipt_date')) {
            Schema::table('erp_sr_item_lot_details', function (Blueprint $table) {
                $table->timestamp('original_receipt_date')->change();
            });
        }
        if (Schema::hasColumn('erp_mr_item_lot_details', 'original_receipt_date')) {
            Schema::table('erp_mr_item_lot_details', function (Blueprint $table) {
                $table->timestamp('original_receipt_date')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        if (Schema::hasColumn('erp_mr_item_lot_details', 'original_receipt_date')) {
            Schema::table('erp_mr_item_lot_details', function (Blueprint $table) {
                $table->date('original_receipt_date')->change();
            });
        }
        if (Schema::hasColumn('erp_sr_item_lot_details', 'original_receipt_date')) {
            Schema::table('erp_sr_item_lot_details', function (Blueprint $table) {
                $table->date('original_receipt_date')->change();
            });
        }
    }
};
