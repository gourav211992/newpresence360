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
        Schema::table('erp_pb_details', function (Blueprint $table) {
            $table->decimal('order_qty', 15, 6)->default(0.00)->after('cost_center_name')->nullable();
            $table->decimal('rejected_qty', 15, 6)->default(0.00)->after('accepted_qty')->nullable();
            $table->decimal('po_rate', 15, 6)->default(0.00)->after('inventory_uom_qty')->nullable();
            $table->decimal('variance', 15, 6)->default(0.00)->after('rate')->nullable();
        });

        Schema::table('erp_pb_detail_histories', function (Blueprint $table) {
            $table->decimal('order_qty', 15, 6)->default(0.00)->after('cost_center_name')->nullable();
            $table->decimal('rejected_qty', 15, 6)->default(0.00)->after('accepted_qty')->nullable();
            $table->decimal('po_rate', 15, 6)->default(0.00)->after('inventory_uom_qty')->nullable();
            $table->decimal('variance', 15, 6)->default(0.00)->after('rate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_pb_detail_histories', function (Blueprint $table) {
            $table->dropColumn('order_qty');
            $table->dropColumn('rejected_qty');
            $table->dropColumn('po_rate');
            $table->dropColumn('variance');
        });

        Schema::table('erp_pb_details', function (Blueprint $table) {
            $table->dropColumn('order_qty');
            $table->dropColumn('rejected_qty');
            $table->dropColumn('po_rate');
            $table->dropColumn('variance');
        });
    }
};
