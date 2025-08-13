<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('erp_po_items')
            ->whereNull('asn_qty')
            ->update(['asn_qty' => 0]);

        Schema::table('erp_po_items', function (Blueprint $table) {
            $table->double('asn_qty', 15, 6)->default(0)->change();
        });
        Schema::table('erp_po_items_history', function (Blueprint $table) {
            $table->double('ge_qty', 15, 6)->default(0)->after('grn_qty');
            $table->double('asn_qty', 15, 6)->default(0)->after('ge_qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_po_items_history', function (Blueprint $table) {
            $table->dropColumn('ge_qty');
            $table->dropColumn('asn_qty');
        });
    }
};
