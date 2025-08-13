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
        Schema::table('erp_pwo_bom_mapping', function (Blueprint $table) {
            $table->unsignedBigInteger('station_id')->nullable()->after('qty');
            $table->unsignedBigInteger('section_id')->nullable()->after('station_id');
            $table->unsignedBigInteger('sub_section_id')->nullable()->after('section_id');
        });
        Schema::table('erp_pwo_bom_mapping_history', function (Blueprint $table) {
            $table->unsignedBigInteger('station_id')->nullable()->after('qty');
            $table->unsignedBigInteger('section_id')->nullable()->after('station_id');
            $table->unsignedBigInteger('sub_section_id')->nullable()->after('section_id');
        });

        Schema::table('erp_mo_bom_mapping', function (Blueprint $table) {
            $table->unsignedBigInteger('station_id')->nullable()->after('qty');
            $table->unsignedBigInteger('section_id')->nullable()->after('station_id');
            $table->unsignedBigInteger('sub_section_id')->nullable()->after('section_id');
        });
        Schema::table('erp_mo_bom_mapping_history', function (Blueprint $table) {
            $table->unsignedBigInteger('station_id')->nullable()->after('qty');
            $table->unsignedBigInteger('section_id')->nullable()->after('station_id');
            $table->unsignedBigInteger('sub_section_id')->nullable()->after('section_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_pwo_bom_mapping_history', function (Blueprint $table) {
            $table->dropColumn('station_id');
            $table->dropColumn('section_id');
            $table->dropColumn('sub_section_id');
        });
        Schema::table('erp_pwo_bom_mapping', function (Blueprint $table) {
            $table->dropColumn('station_id');
            $table->dropColumn('section_id');
            $table->dropColumn('sub_section_id');
        });

        Schema::table('erp_mo_bom_mapping_history', function (Blueprint $table) {
            $table->dropColumn('station_id');
            $table->dropColumn('section_id');
            $table->dropColumn('sub_section_id');
        });
        Schema::table('erp_mo_bom_mapping', function (Blueprint $table) {
            $table->dropColumn('station_id');
            $table->dropColumn('section_id');
            $table->dropColumn('sub_section_id');
        });

    }
};
