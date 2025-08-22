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
        Schema::table('erp_invoice_items', function (Blueprint $table) {
            $table->unsignedBigInteger('pl_item_id') -> nullable() -> index() -> after('sale_invoice_id');
            $table->unsignedBigInteger('pl_item_detail_id') -> nullable() -> index() -> after('sale_invoice_id');
        });
        Schema::table('erp_invoice_items_history', function (Blueprint $table) {
            $table->unsignedBigInteger('pl_item_id') -> nullable() -> index() -> after('sale_invoice_id');
            $table->unsignedBigInteger('pl_item_detail_id') -> nullable() -> index() -> after('sale_invoice_id');
        });
        Schema::table('erp_pl_item_details', function (Blueprint $table) {
            $table->double('dnote_qty', 20, 6) -> default(0) -> after('picked_qty');
        });
        Schema::table('erp_pl_item_details_history', function (Blueprint $table) {
            $table->double('dnote_qty', 20, 6) -> default(0) -> after('picked_qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_invoice_items', function (Blueprint $table) {
            $table->dropColumn(['pl_item_id', 'pl_item_detail_id']);
        });
        Schema::table('erp_invoice_items_history', function (Blueprint $table) {
            $table->dropColumn(['pl_item_id', 'pl_item_detail_id']);
        });
        Schema::table('erp_pl_item_details', function (Blueprint $table) {
            $table->dropColumn(['dnote_qty']);
        });
        Schema::table('erp_pl_item_details_history', function (Blueprint $table) {
            $table->dropColumn(['dnote_qty']);
        });
    }
};
