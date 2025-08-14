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
        Schema::table('erp_boms', function (Blueprint $table) {
            $table->unsignedBigInteger('production_bom_id')->nullable()->comment('erp_boms id')->after('type');
        });
        Schema::table('erp_boms_history', function (Blueprint $table) {
            $table->unsignedBigInteger('production_bom_id')->nullable()->comment('erp_boms id')->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_boms', function (Blueprint $table) {
            $table->dropColumn('production_bom_id');
        });
        Schema::table('erp_boms_history', function (Blueprint $table) {
            $table->dropColumn('production_bom_id');
        });
    }
};
