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
        // Adding pi_item_id to erp_po_items table
        if (!Schema::hasColumn('erp_po_items', 'pi_item_id')) {
            Schema::table('erp_po_items', function (Blueprint $table) {
                $table->unsignedBigInteger('pi_item_id')->nullable()->after('purchase_order_id');
            });
        }

        // Adding pi_item_id to erp_po_items_history table
        if (!Schema::hasColumn('erp_po_items_history', 'pi_item_id')) {
            Schema::table('erp_po_items_history', function (Blueprint $table) {
                $table->unsignedBigInteger('pi_item_id')->nullable()->after('purchase_order_id');
            });
        }

        // Adding indent_qty to erp_pi_items table
        if (!Schema::hasColumn('erp_pi_items', 'indent_qty')) {
            Schema::table('erp_pi_items', function (Blueprint $table) {
                $table->decimal('indent_qty', 15, 2)->default(0.00)->after('vendor_name');
            });
        }

        // Adding indent_qty to erp_pi_items_history table
        if (!Schema::hasColumn('erp_pi_items_history', 'indent_qty')) {
            Schema::table('erp_pi_items_history', function (Blueprint $table) {
                $table->decimal('indent_qty', 15, 2)->default(0.00)->after('vendor_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_po_items', function (Blueprint $table) {
            $table->dropColumn('pi_item_id');
        });
        Schema::table('erp_po_items_history', function (Blueprint $table) {
            $table->dropColumn('pi_item_id');
        });

        Schema::table('erp_pi_items', function (Blueprint $table) {
            $table->dropColumn('indent_qty');
        });
        Schema::table('erp_pi_items_history', function (Blueprint $table) {
            $table->dropColumn('indent_qty');
        });
    }
};
