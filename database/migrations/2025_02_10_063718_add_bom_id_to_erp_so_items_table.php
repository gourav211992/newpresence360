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
            $table->unsignedBigInteger('bom_id')->nullable()->after('sale_order_id');
        });
        Schema::table('erp_so_items_history', function (Blueprint $table) {
            $table->unsignedBigInteger('bom_id')->nullable()->after('sale_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_so_items', function (Blueprint $table) {
            $table->dropColumn(['bom_id']);
        });
        Schema::table('erp_so_items_history', function (Blueprint $table) {
            $table->dropColumn(['bom_id']);
        });
    }
};
