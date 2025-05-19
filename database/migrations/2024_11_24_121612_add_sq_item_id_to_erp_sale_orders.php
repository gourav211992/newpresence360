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
        Schema::table('erp_so_items', function (Blueprint $table) {
            $table->unsignedBigInteger('sq_item_id') -> nullable()->after('order_quotation_id');
        });
        Schema::table('erp_so_items_history', function (Blueprint $table) {
            $table->unsignedBigInteger('sq_item_id') -> nullable()->after('order_quotation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_so_items', function (Blueprint $table) {
            $table->dropColumn(['sq_item_id']);
        });
        Schema::table('erp_so_items_history', function (Blueprint $table) {
            $table->dropColumn(['sq_item_id']);
        });
    }
};
