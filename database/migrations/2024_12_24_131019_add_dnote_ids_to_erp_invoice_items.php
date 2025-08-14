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
            $table->unsignedBigInteger('dnote_id')->nullable()->after('sale_order_id');
            $table->unsignedBigInteger('dnote_item_id')->nullable()->after('si_item_id');
        });
        Schema::table('erp_invoice_items_history', function (Blueprint $table) {
            $table->unsignedBigInteger('dnote_id')->nullable()->after('sale_order_id');
            $table->unsignedBigInteger('dnote_item_id')->nullable()->after('si_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_invoice_items', function (Blueprint $table) {
            $table->dropColumn(['dnote_id', 'dnote_item_id']);
        });
        Schema::table('erp_invoice_items_history', function (Blueprint $table) {
            $table->dropColumn(['dnote_id', 'dnote_item_id']);
        });
    }
};
