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
        Schema::table('erp_po_items', function (Blueprint $table) {
            $table->double('invoice_quantity',[20,6])->default(0.00)->after('grn_qty');
            $table->unsignedBigInteger('po_item_id')->nullable()->after('pi_item_id');
        });
        Schema::table('erp_po_items_history', function (Blueprint $table) {
            $table->double('invoice_quantity',[20,6])->default(0.00)->after('grn_qty');
            $table->unsignedBigInteger('po_item_id')->nullable()->after('pi_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_po_items', function (Blueprint $table) {
            $table->dropColumn('invoice_quantity');
            $table->dropColumn('po_item_id');
        });
        Schema::table('erp_po_items_history', function (Blueprint $table) {
            $table->dropColumn('invoice_quantity');
            $table->dropColumn('po_item_id');
        });
    }
};
