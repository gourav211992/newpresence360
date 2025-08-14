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
            $table->unsignedBigInteger('si_item_id')->default(null)->after('so_item_id');
        });
        Schema::table('erp_invoice_items_history', function (Blueprint $table) {
            $table->unsignedBigInteger('si_item_id')->default(null)->after('so_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_invoice_items', function (Blueprint $table) {
            $table->dropColumn(['si_item_id']);
        });
        Schema::table('erp_invoice_items_history', function (Blueprint $table) {
            $table->dropColumn(['si_item_id']);
        });
    }
};
