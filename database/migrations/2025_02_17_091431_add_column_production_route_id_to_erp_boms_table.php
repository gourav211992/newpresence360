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
            $table->unsignedBigInteger('production_route_id')->nullable()->after('production_bom_id');

            $table->foreign('production_route_id')->references('id')->on('erp_production_routes')->onDelete('cascade');            
        });
        Schema::table('erp_boms_history', function (Blueprint $table) {
            $table->unsignedBigInteger('production_route_id')->nullable()->after('production_bom_id');

            $table->foreign('production_route_id')->references('id')->on('erp_production_routes')->onDelete('cascade');            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_boms_history', function (Blueprint $table) {
            $table->dropForeign(['production_route_id']);
            $table->dropColumn('production_route_id');
        });
        Schema::table('erp_boms', function (Blueprint $table) {
            $table->dropForeign(['production_route_id']);
            $table->dropColumn('production_route_id');
        });
    }
};
