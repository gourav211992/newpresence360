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
        $tables = [
            'erp_po_items' => 'purchase_order_id',
            'erp_po_items_history' => 'purchase_order_id',
            'erp_pi_items' => 'pi_id',
            'erp_pi_items_history' => 'pi_id',
            'erp_pi_po_mapping' => 'po_item_id',
        ];

        foreach ($tables as $tableName => $afterColumn) {
            if (Schema::hasColumn($tableName, 'so_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('so_id');
                });
            }
            Schema::table($tableName, function (Blueprint $table) use ($afterColumn) {
                $table->unsignedBigInteger('so_id')->nullable()->after($afterColumn);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'erp_po_items',
            'erp_po_items_history',
            'erp_pi_items',
            'erp_pi_items_history',
            'erp_pi_po_mapping',
        ];
        foreach ($tables as $tableName) {
            if (Schema::hasColumn($tableName, 'so_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('so_id');
                });
            }
        }
    }
};
