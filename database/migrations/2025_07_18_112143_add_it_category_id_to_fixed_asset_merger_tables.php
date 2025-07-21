<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddItCategoryIdToFixedAssetMergerTables extends Migration
{
    public function up(): void
    {
        Schema::table('erp_finance_fixed_asset_merger', function (Blueprint $table) {
            $table->unsignedBigInteger('it_category_id')->nullable()->after('category_id');
        });

        Schema::table('erp_finance_fixed_asset_merger_history', function (Blueprint $table) {
            $table->unsignedBigInteger('it_category_id')->nullable()->after('category_id');
        });
    }

    public function down(): void
    {
        Schema::table('erp_finance_fixed_asset_merger', function (Blueprint $table) {
            $table->dropColumn('it_category_id');
        });

        Schema::table('erp_finance_fixed_asset_merger_history', function (Blueprint $table) {
            $table->dropColumn('it_category_id');
        });
    }
}
