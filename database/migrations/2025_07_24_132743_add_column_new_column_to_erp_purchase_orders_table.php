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
        if (!Schema::hasColumn('erp_purchase_orders', 'po_type')) {
            // Info log
            echo "\n Adding po_type column to erp_purchase_orders table";
            Schema::table('erp_purchase_orders', function (Blueprint $table) {
                $table->string('po_type', 40)->default('Goods')->after('id')->index();
            });
        }
        if (!Schema::hasColumn('erp_purchase_orders_history', 'po_type')) {
            Schema::table('erp_purchase_orders_history', function (Blueprint $table) {
                $table->string('po_type', 40)->default('Goods')->after('id')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('erp_purchase_orders_history', 'po_type')) {
            Schema::table('erp_purchase_orders_history', function (Blueprint $table) {
                $table->dropIndex(['po_type']);
                $table->dropColumn('po_type');
            });
        }

        if (Schema::hasColumn('erp_purchase_orders', 'po_type')) {
            Schema::table('erp_purchase_orders', function (Blueprint $table) {
                $table->dropIndex(['po_type']);
                $table->dropColumn('po_type');
            });
        }
    }
};
