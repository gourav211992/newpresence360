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
            $table->dropColumn('mo_id');
            $table->dropColumn('mo_production_item_id');
        });
        Schema::table('stock_ledger_reservations', function (Blueprint $table) {
            $table->unsignedBigInteger('pslip_id')->nullable()->after('stock_ledger_id');
            $table->unsignedBigInteger('pslip_item_id')->nullable()->after('pslip_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_ledger_reservations', function (Blueprint $table) {
            $table->dropColumn('pslip_item_id');
            $table->dropColumn('pslip_id');
        });
        Schema::table('stock_ledger_reservations', function (Blueprint $table) {
            $table->unsignedBigInteger('mo_id')->nullable()->after('stock_ledger_id');
            $table->unsignedBigInteger('mo_production_item_id')->nullable()->after('mo_id');
        });
    }
};
