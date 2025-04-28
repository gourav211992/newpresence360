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
        Schema::table('erp_pwo_so_mapping', function (Blueprint $table) {
            $table->unsignedBigInteger('so_id')->nullable()->change();
            $table->unsignedBigInteger('so_item_id')->nullable()->change();
            $table->unsignedBigInteger('bom_id')->after('so_item_id')->nullable();
            $table->unsignedBigInteger('production_route_id')->after('bom_id')->nullable();

            $table->foreign('bom_id')->references('id')->on('erp_boms')->onDelete('cascade');
            $table->foreign('production_route_id')->references('id')->on('erp_production_routes')->onDelete('cascade');
        });
        Schema::table('erp_pwo_so_mapping_history', function (Blueprint $table) {
            $table->unsignedBigInteger('so_id')->nullable()->change();
            $table->unsignedBigInteger('so_item_id')->nullable()->change();
            $table->unsignedBigInteger('bom_id')->after('so_item_id')->nullable();
            $table->unsignedBigInteger('production_route_id')->after('bom_id')->nullable();

            $table->foreign('bom_id')->references('id')->on('erp_boms_history')->onDelete('cascade');
            $table->foreign('production_route_id')->references('id')->on('erp_production_routes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_pwo_so_mapping_history', function (Blueprint $table) {
            $table->dropForeign(['bom_id']);
            $table->dropForeign(['production_route_id']);
            $table->dropColumn('bom_id');
            $table->dropColumn('production_route_id');

            $table->unsignedBigInteger('so_id')->change();
            $table->unsignedBigInteger('so_item_id')->change();
        });

        Schema::table('erp_pwo_so_mapping', function (Blueprint $table) {
            $table->dropForeign(['bom_id']);
            $table->dropForeign(['production_route_id']);
            $table->dropColumn('bom_id');
            $table->dropColumn('production_route_id');

            $table->unsignedBigInteger('so_id')->change();
            $table->unsignedBigInteger('so_item_id')->change();
        });
    }
};
