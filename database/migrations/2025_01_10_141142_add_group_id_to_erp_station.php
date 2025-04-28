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
        Schema::table('erp_stations', function (Blueprint $table) {
            $table->unsignedBigInteger('station_group_id')->nullable();
            $table->foreign('station_group_id')->references('id')->on('erp_station_groups')->onDelete('set null'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_stations', function (Blueprint $table) {
            $table->dropForeign(['station_group_id']);
            $table->dropColumn('station_group_id');
        });
    }
};
