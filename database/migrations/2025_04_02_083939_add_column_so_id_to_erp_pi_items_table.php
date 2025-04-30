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
        if (Schema::hasColumn('erp_pi_items', 'so_item_id')) {
            Schema::table('erp_pi_items', function (Blueprint $table) {
                $table->dropColumn('so_item_id');
            });
        }
        if (!Schema::hasColumn('erp_pi_items', 'so_id')) {
            Schema::table('erp_pi_items', function (Blueprint $table) {
                $table->json('so_id')->nullable()->after('pi_id');
            });
        }

        if (Schema::hasColumn('erp_pi_items_history', 'so_item_id')) {
            Schema::table('erp_pi_items_history', function (Blueprint $table) {
                $table->dropColumn('so_item_id');
            });
        }
        if (!Schema::hasColumn('erp_pi_items_history', 'so_id')) {
            Schema::table('erp_pi_items_history', function (Blueprint $table) {
                $table->json('so_id')->nullable()->after('pi_id');
            });
        }

        if (Schema::hasColumn('erp_pi_po_mapping', 'so_item_id')) {
            Schema::table('erp_pi_po_mapping', function (Blueprint $table) {
                $table->dropColumn('so_item_id');
            });
        }
        if (!Schema::hasColumn('erp_pi_po_mapping', 'so_id')) {
            Schema::table('erp_pi_po_mapping', function (Blueprint $table) {
                $table->json('so_id')->nullable()->after('po_item_id');
            });
        }

        if (Schema::hasColumn('erp_po_items', 'so_item_id')) {
            Schema::table('erp_po_items', function (Blueprint $table) {
                $table->dropColumn('so_item_id');
            });
        }

        if (Schema::hasColumn('erp_po_items_history', 'so_item_id')) {
            Schema::table('erp_po_items_history', function (Blueprint $table) {
                $table->dropColumn('so_item_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('erp_po_items_history', 'so_id')) {
            Schema::table('erp_po_items_history', function (Blueprint $table) {
                $table->dropColumn('so_id');
            });
        }

        if (Schema::hasColumn('erp_po_items', 'so_id')) {
            Schema::table('erp_po_items', function (Blueprint $table) {
                $table->dropColumn('so_id');
            });
        }

        if (Schema::hasColumn('erp_pi_items_history', 'so_id')) {
            Schema::table('erp_pi_items_history', function (Blueprint $table) {
                $table->dropColumn('so_id');
            });
        }

        if (Schema::hasColumn('erp_pi_items', 'so_id')) {
            Schema::table('erp_pi_items', function (Blueprint $table) {
                $table->dropColumn('so_id');
            });
        }

        if (Schema::hasColumn('erp_pi_po_mapping', 'so_id')) {
            Schema::table('erp_pi_po_mapping', function (Blueprint $table) {
                $table->dropColumn('so_id');
            });
        }
    }
};
