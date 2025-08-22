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
        Schema::table('erp_material_issue_header', function (Blueprint $table) {
            $table->dropColumn(['station_id']);
            $table->unsignedBigInteger('from_station_id')->after('from_sub_store_id')->nullable();
            $table->unsignedBigInteger('to_station_id')->after('to_sub_store_id')->nullable();
        });
        Schema::table('erp_mi_items', function (Blueprint $table) {
            $table->unsignedBigInteger('from_station_id')->after('from_sub_store_id')->nullable();
            $table->unsignedBigInteger('to_station_id')->after('to_sub_store_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_material_issue_header', function (Blueprint $table) {
            $table->unsignedBigInteger('station_id')->after('issue_type');
            $table->dropColumn(['from_station_id', 'to_station_id']);
        });
        Schema::table('erp_mi_items', function (Blueprint $table) {
            $table->dropColumn(['from_station_id', 'to_station_id']);
        });
    }
};
