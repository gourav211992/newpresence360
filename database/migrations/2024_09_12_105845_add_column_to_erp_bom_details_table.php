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
            $table->unsignedBigInteger('sub_section_id')->nullable()->after('total_amount')->comment('erp_product_section_details id');
            $table->string('section_name')->nullable()->after('sub_section_id');
            $table->string('sub_section_name')->nullable()->after('section_name');
            $table->string('station_name')->nullable()->after('sub_section_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_bom_details', function (Blueprint $table) {
            $table->dropColumn('sub_section_id');
            $table->dropColumn('section_name');
            $table->dropColumn('sub_section_name');
            $table->dropColumn('station_name');
        });
    }
};
