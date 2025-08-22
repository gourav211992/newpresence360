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
        Schema::table('erp_mo_items', function (Blueprint $table) {
            $table->unsignedBigInteger('bom_detail_id')->nullable()->after('mo_id');
            $table->unsignedBigInteger('station_id')->nullable()->after('bom_detail_id');
        });
        Schema::table('erp_mo_items_history', function (Blueprint $table) {
            $table->unsignedBigInteger('bom_detail_id')->nullable()->after('mo_id');
            $table->unsignedBigInteger('station_id')->nullable()->after('bom_detail_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_mo_items_history', function (Blueprint $table) {
            $table->dropColumn('bom_detail_id');
            $table->dropColumn('station_id');
        });

        Schema::table('erp_mo_items', function (Blueprint $table) {
            $table->dropColumn('bom_detail_id');
            $table->dropColumn('station_id');
        });
    }
};
