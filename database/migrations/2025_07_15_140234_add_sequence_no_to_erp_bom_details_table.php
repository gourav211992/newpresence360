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
            $table->unsignedSmallInteger('sequence_no')->nullable()->after('id')->index();
        });
        Schema::table('erp_bom_details_history', function (Blueprint $table) {
            $table->unsignedSmallInteger('sequence_no')->nullable()->after('source_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_bom_details_history', function (Blueprint $table) {
            $table->dropIndex(['sequence_no']);
            $table->dropColumn('sequence_no');
        });
        Schema::table('erp_bom_details', function (Blueprint $table) {
            $table->dropIndex(['sequence_no']);
            $table->dropColumn('sequence_no');
        });
    }
};
