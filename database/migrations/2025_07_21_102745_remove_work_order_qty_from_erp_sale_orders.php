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
            if (Schema::hasColumn('erp_so_items', 'work_order_qty')) {
                $table->dropColumn('work_order_qty');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_so_items', function (Blueprint $table) {
            if (!Schema::hasColumn('erp_so_items', 'work_order_qty')) {
                $table->double('work_order_qty', 20, 6);
            }
        });
    }
};
