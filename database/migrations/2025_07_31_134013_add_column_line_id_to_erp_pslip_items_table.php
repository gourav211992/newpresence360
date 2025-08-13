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
        Schema::table('erp_pslip_items', function (Blueprint $table) {
            $table->unsignedBigInteger('station_line_id')->nullable()->after('cycle_count');
            $table->string('supervisor_name')->nullable()->after('station_line_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_pslip_items', function (Blueprint $table) {
            $table->dropColumn('supervisor_name');
            $table->dropColumn('station_line_id');
        });
    }
};
