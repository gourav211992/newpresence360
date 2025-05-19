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
        Schema::table('erp_land_plots', function (Blueprint $table) {
            $table->morphs('landable');
            $table->integer('approvalLevel')->default(0);
            $table->string('approvalStatus')->default('completed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_land_plots', function (Blueprint $table) {
            $table->dropMorphs('landable');
            $table->dropColumn('approvalLevel');
            $table->dropColumn('approvalStatus');
        });
    }
};
