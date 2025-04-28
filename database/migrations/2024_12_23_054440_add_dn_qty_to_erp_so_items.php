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
            $table->decimal('dnote_qty', 15, 2) -> default(0) -> after('invoice_qty');
        });
        Schema::table('erp_so_items_history', function (Blueprint $table) {
            $table->decimal('dnote_qty', 15, 2) -> default(0) -> after('invoice_qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_so_items', function (Blueprint $table) {
            $table->dropColumn(['dnote_qty']);
        });
        Schema::table('erp_so_items_history', function (Blueprint $table) {
            $table->dropColumn(['dnote_qty']);
        });
    }
};
