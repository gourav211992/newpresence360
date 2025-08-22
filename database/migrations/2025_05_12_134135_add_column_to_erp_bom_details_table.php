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
        Schema::table('erp_bom_details', function (Blueprint $table) {
            $table->unsignedBigInteger('vendor_id')->nullable()->after('station_name');
        });
        Schema::table('erp_bom_details_history', function (Blueprint $table) {
            $table->unsignedBigInteger('vendor_id')->nullable()->after('station_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_bom_details_history', function (Blueprint $table) {
            $table->dropColumn('vendor_id');
        });
        Schema::table('erp_bom_details', function (Blueprint $table) {
            $table->dropColumn('vendor_id');
        });
    }
};
