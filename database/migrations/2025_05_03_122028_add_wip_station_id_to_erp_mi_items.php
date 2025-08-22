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
        Schema::table('erp_mi_items', function (Blueprint $table) {
            $table -> unsignedInteger('wip_station_id') -> after('item_id') -> nullable();
        });
        Schema::table('erp_mi_items_history', function (Blueprint $table) {
            $table -> unsignedInteger('wip_station_id') -> after('item_id') -> nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_mi_items', function (Blueprint $table) {
            $table -> dropColumn(['wip_station_id']);
        });
        Schema::table('erp_mi_items_history', function (Blueprint $table) {
            $table -> dropColumn(['wip_station_id']);
        });
    }
};
