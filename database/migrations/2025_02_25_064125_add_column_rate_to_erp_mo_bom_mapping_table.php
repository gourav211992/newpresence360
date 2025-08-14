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
            $table->double('rate',20,6)->default(0)->after('qty');
        });
        Schema::table('erp_mo_bom_mapping_history', function (Blueprint $table) {
            $table->double('rate',20,6)->default(0)->after('qty');
        });

        Schema::table('erp_mo_products', function (Blueprint $table) {
            $table->double('rate',20,6)->default(0)->after('qty');
        });
        Schema::table('erp_mo_products_history', function (Blueprint $table) {
            $table->double('rate',20,6)->default(0)->after('qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_mo_bom_mapping_history', function (Blueprint $table) {
            $table->dropColumn('rate');
        });
        Schema::table('erp_mo_bom_mapping', function (Blueprint $table) {
            $table->dropColumn('rate');
        });
        Schema::table('erp_mo_products_history', function (Blueprint $table) {
            $table->dropColumn('rate');
        });
        Schema::table('erp_mo_products', function (Blueprint $table) {
            $table->dropColumn('rate');
        });
    }
};
