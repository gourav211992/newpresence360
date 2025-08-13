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
            $table -> decimal('plist_qty' ,20, 6) -> after('pslip_qty') -> default(0);
            $table -> unsignedBigInteger('plist_item_id') -> after('plist_qty') -> nullable();
        });
        Schema::table('erp_so_items_history', function (Blueprint $table) {
            $table -> decimal('plist_qty' ,20, 6) -> after('pslip_qty') -> default(0);
            $table -> unsignedBigInteger('plist_item_id') -> after('plist_qty') -> nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_so_items', function (Blueprint $table) {
            $table -> dropColumn(['plist_qty', 'plist_item_id']);
        });
        Schema::table('erp_so_items_history', function (Blueprint $table) {
            $table -> dropColumn(['plist_qty', 'plist_item_id']);
        });
    }
};
