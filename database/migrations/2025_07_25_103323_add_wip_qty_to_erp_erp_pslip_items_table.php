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
        if (!Schema::hasColumn('erp_pslip_items', 'wip_qty')){
            Schema::table('erp_pslip_items', function (Blueprint $table) {
                $table->decimal('wip_qty', 20, 6)->default(0)->after('rejected_qty')->comment('WIP Quantity');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('erp_pslip_items', 'wip_qty')) {
            Schema::table('erp_pslip_items', function (Blueprint $table) {
                $table->dropColumn('wip_qty');
            });
        }
    }
};
