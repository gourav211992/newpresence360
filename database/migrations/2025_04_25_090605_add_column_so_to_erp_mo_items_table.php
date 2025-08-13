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
        Schema::table('erp_mo_bom_mapping', function (Blueprint $table) {
            $table->unsignedBigInteger('so_id')->nullable()->after('bom_detail_id');
        });
        Schema::table('erp_mo_bom_mapping_history', function (Blueprint $table) {
            $table->unsignedBigInteger('so_id')->nullable()->after('bom_detail_id');
        });
        Schema::table('erp_mo_items', function (Blueprint $table) {
            $table->unsignedBigInteger('so_id')->nullable()->after('mo_id');
            $table->double('consumed_qty',20,6)->default(0)->after('qty');
        });
        Schema::table('erp_mo_items_history', function (Blueprint $table) {
            $table->unsignedBigInteger('so_id')->nullable()->after('mo_id');
            $table->double('consumed_qty',20,6)->default(0)->after('qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_mo_bom_mapping_history', function (Blueprint $table) {
            $table->dropColumn('so_id');
        });
        Schema::table('erp_mo_bom_mapping', function (Blueprint $table) {
            $table->dropColumn('so_id');
        });
        Schema::table('erp_mo_items_history', function (Blueprint $table) {
            $table->dropColumn('so_id');
            $table->dropColumn('consumed_qty');
        });
        Schema::table('erp_mo_items', function (Blueprint $table) {
            $table->dropColumn('so_id');
            $table->dropColumn('consumed_qty');
        });
    }
};
