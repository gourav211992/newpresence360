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
        Schema::table('erp_production_routes', function (Blueprint $table) {
            $table->double('safety_buffer_perc',20,6)->nullable()->after('description');
        });
        Schema::table('erp_boms_history', function (Blueprint $table) {
            $table->double('safety_buffer_perc',20,6)->nullable()->after('header_overhead_amount');
        });
        Schema::table('erp_boms', function (Blueprint $table) {
            $table->double('safety_buffer_perc',20,6)->nullable()->after('header_overhead_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_production_routes', function (Blueprint $table) {
            $table->dropColumn('safety_buffer_perc');
        });
        Schema::table('erp_boms_history', function (Blueprint $table) {
            $table->dropColumn('safety_buffer_perc');
        });
        Schema::table('erp_boms', function (Blueprint $table) {
            $table->dropColumn('safety_buffer_perc');
        });
    }
};
