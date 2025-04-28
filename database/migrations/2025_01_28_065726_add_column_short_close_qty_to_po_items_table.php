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
            $table->double('short_close_qty', 15, 2)->default(0.00)->after('invoice_quantity');
        });
        Schema::table('erp_po_items_history', function (Blueprint $table) {
            $table->double('short_close_qty', 15, 2)->default(0.00)->after('invoice_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_po_items', function (Blueprint $table) {
            $table->dropColumn('short_close_qty');
        });
        Schema::table('erp_po_items_history', function (Blueprint $table) {
            $table->dropColumn('short_close_qty');
        });
    }
};
