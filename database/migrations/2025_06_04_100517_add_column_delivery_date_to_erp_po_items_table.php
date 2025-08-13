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
        Schema::table('erp_po_items', function (Blueprint $table) {
            $table->date('delivery_date')->nullable()->after('remarks');
        });
        Schema::table('erp_po_items_history', function (Blueprint $table) {
            $table->date('delivery_date')->nullable()->after('remarks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_po_items_history', function (Blueprint $table) {
            $table->dropColumn('delivery_date');
        });
        Schema::table('erp_po_items', function (Blueprint $table) {
            $table->dropColumn('delivery_date');
        });
    }
};
