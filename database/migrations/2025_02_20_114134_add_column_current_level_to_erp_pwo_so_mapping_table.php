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
        Schema::table('erp_pwo_so_mapping', function (Blueprint $table) {
            $table->integer('current_level')->default(1)->after('inventory_uom_qty');
        });
        Schema::table('erp_pwo_station_consumptions', function (Blueprint $table) {
            $table->integer('level')->default(1)->after('mo_value');
        });
        Schema::table('erp_pwo_station_consumptions_history', function (Blueprint $table) {
            $table->integer('level')->default(1)->after('mo_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_pwo_so_mapping', function (Blueprint $table) {
            $table->dropColumn('current_level');
        });
        Schema::table('erp_pwo_station_consumptions_history', function (Blueprint $table) {
            $table->dropColumn('level');
        });
        Schema::table('erp_pwo_station_consumptions', function (Blueprint $table) {
            $table->dropColumn('level');
        });
    }
};
