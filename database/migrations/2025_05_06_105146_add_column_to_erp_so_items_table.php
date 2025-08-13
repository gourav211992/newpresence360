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
        if (!Schema::hasColumn('erp_so_items', 'picked_qty')) {
            Schema::table('erp_so_items', function (Blueprint $table) {
            $table->decimal('picked_qty', 20, 6)->default(0);
            });
        }
        if (!Schema::hasColumn('erp_so_items_history', 'picked_qty')) {
            Schema::table('erp_so_items_history', function (Blueprint $table) {
            $table->decimal('picked_qty', 20, 6)->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_so_items', function (Blueprint $table) {
            //
            if (Schema::hasColumn('erp_so_items', 'picked_qty')) {
                $table->dropColumn('picked_qty');
            }
        });
        Schema::table('erp_so_items_history', function (Blueprint $table) {
            //
            if (Schema::hasColumn('erp_so_items_history', 'picked_qty')) {
                $table->dropColumn('picked_qty');
            }
        });
    }
};
