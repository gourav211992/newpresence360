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
            $table->double('pslip_qty', 20, 6) -> default(0) -> after('pwo_qty');
        });
        Schema::table('erp_so_items_history', function (Blueprint $table) {
            $table->double('pslip_qty', 20, 6) -> default(0) -> after('pwo_qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_so_items', function (Blueprint $table) {
            $table->dropColumn(['pslip_qty']);
        });
        Schema::table('erp_so_items_history', function (Blueprint $table) {
            $table->dropColumn(['pslip_qty']);
        });
    }
};
