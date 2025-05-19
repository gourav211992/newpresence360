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
        Schema::table('erp_po_items_history', function (Blueprint $table) {
            $table->dropForeign(['purchase_order_id']);
            $table->foreign('purchase_order_id')
                  ->references('id')
                  ->on('erp_purchase_orders_history')
                  ->onDelete('cascade');
        });

        Schema::table('erp_po_item_attributes_history', function (Blueprint $table) {
            $table->dropForeign(['po_item_id']);
            $table->dropForeign(['purchase_order_id']);

            $table->foreign('purchase_order_id')
                  ->references('id')
                  ->on('erp_purchase_orders_history')
                  ->onDelete('cascade');

            $table->foreign('po_item_id')
                  ->references('id')
                  ->on('erp_po_items_history')
                  ->onDelete('cascade');
        });

        Schema::table('erp_po_terms_history', function (Blueprint $table) {
            $table->dropForeign(['purchase_order_id']);

            $table->foreign('purchase_order_id')
                  ->references('id')
                  ->on('erp_purchase_orders_history')
                  ->onDelete('cascade');
        });

        Schema::table('erp_purchase_order_ted_history', function (Blueprint $table) {
            $table->dropForeign(['po_item_id']);
            $table->dropForeign(['purchase_order_id']);

            $table->foreign('purchase_order_id')
                  ->references('id')
                  ->on('erp_purchase_orders_history')
                  ->onDelete('cascade');
            $table->foreign('po_item_id')
                  ->references('id')
                  ->on('erp_po_items_history')
                  ->onDelete('cascade');
        });

        Schema::table('erp_po_item_delivery_history', function (Blueprint $table) {
            $table->dropForeign(['po_item_id']);
            $table->dropForeign(['purchase_order_id']);

            $table->foreign('purchase_order_id')
                  ->references('id')
                  ->on('erp_purchase_orders_history')
                  ->onDelete('cascade');
            $table->foreign('po_item_id')
                  ->references('id')
                  ->on('erp_po_items_history')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_history', function (Blueprint $table) {
            $table->dropForeign(['purchase_order_id']);
            $table->foreign('purchase_order_id')
                  ->references('id')
                  ->on('erp_purchase_orders')
                  ->onDelete('cascade');
        });

        Schema::table('erp_po_item_attributes_history', function (Blueprint $table) {
            $table->dropForeign(['po_item_id']);
            $table->dropForeign(['purchase_order_id']);
            $table->foreign('purchase_order_id')
                  ->references('id')
                  ->on('erp_purchase_orders')
                  ->onDelete('cascade');
            $table->foreign('po_item_id')
                  ->references('id')
                  ->on('erp_po_items')
                  ->onDelete('cascade');
        });

        Schema::table('erp_po_terms_history', function (Blueprint $table) {
            $table->dropForeign(['purchase_order_id']);
            $table->foreign('purchase_order_id')
                  ->references('id')
                  ->on('erp_purchase_orders')
                  ->onDelete('cascade');
        });


        Schema::table('erp_purchase_order_ted_history', function (Blueprint $table) {
            $table->dropForeign(['po_item_id']);
            $table->dropForeign(['purchase_order_id']);
            $table->foreign('purchase_order_id')
                  ->references('id')
                  ->on('erp_purchase_orders')
                  ->onDelete('cascade');
            $table->foreign('po_item_id')
                  ->references('id')
                  ->on('erp_po_items')
                  ->onDelete('cascade');
        });

        Schema::table('erp_po_item_delivery_history', function (Blueprint $table) {
            $table->dropForeign(['po_item_id']);
            $table->dropForeign(['purchase_order_id']);
            $table->foreign('purchase_order_id')
                  ->references('id')
                  ->on('erp_purchase_orders')
                  ->onDelete('cascade');
            $table->foreign('po_item_id')
                  ->references('id')
                  ->on('erp_po_items')
                  ->onDelete('cascade');
        });
    }
};
