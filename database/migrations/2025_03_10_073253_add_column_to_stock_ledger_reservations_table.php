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
        Schema::table('stock_ledger_reservations', function (Blueprint $table) {
            $table->dropColumn('order_id');
            $table->unsignedBigInteger('mo_id')->nullable()->index()->after('stock_ledger_id');
            $table->unsignedBigInteger('mo_production_item_id')->nullable()->index()->after('mo_id');
            $table->unsignedBigInteger('so_id')->nullable()->index()->after('mo_production_item_id');
            $table->unsignedBigInteger('so_item_id')->nullable()->index()->after('so_id');
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_ledger_reservations', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id');
            $table->dropIndex(['mo_id']);
            $table->dropColumn('mo_id');
            $table->dropIndex(['mo_production_item_id']);
            $table->dropColumn('mo_production_item_id');
            $table->dropIndex(['so_id']);
            $table->dropColumn(['so_id']);
            $table->dropIndex(['so_item_id']);
            $table->dropColumn('so_item_id');
        });
    }
};
