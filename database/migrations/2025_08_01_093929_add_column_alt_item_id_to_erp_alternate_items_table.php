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
        if (!Schema::hasColumn('erp_alternate_items', 'alt_item_id')) {
            Schema::table('erp_alternate_items', function (Blueprint $table) {
                $table->unsignedBigInteger('alt_item_id')->nullable()->after('item_id');
            });
        }
        if (!Schema::hasColumn('erp_alternate_items_history', 'alt_item_id')) {
            Schema::table('erp_alternate_items_history', function (Blueprint $table) {
                $table->unsignedBigInteger('alt_item_id')->nullable()->after('item_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('erp_alternate_items_history', 'alt_item_id')) {
            Schema::table('erp_alternate_items_history', function (Blueprint $table) {
                $table->dropColumn('alt_item_id');
            });
        }
        if (Schema::hasColumn('erp_alternate_items', 'alt_item_id')) {
            Schema::table('erp_alternate_items', function (Blueprint $table) {
                $table->dropColumn('alt_item_id');
            });
        }
    }
};
