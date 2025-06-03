<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('erp_finance_fixed_asset_split', function (Blueprint $table) {
        $table->unsignedBigInteger('old_category_id')->nullable()->after('category_id');
    });

    Schema::table('erp_finance_fixed_asset_split_history', function (Blueprint $table) {
        $table->unsignedBigInteger('old_category_id')->nullable()->after('category_id');
    });

    Schema::table('erp_finance_fixed_asset_merger', function (Blueprint $table) {
        $table->unsignedBigInteger('old_category_id')->nullable()->after('category_id');
    });

    Schema::table('erp_finance_fixed_asset_merger_history', function (Blueprint $table) {
        $table->unsignedBigInteger('old_category_id')->nullable()->after('category_id');
    });
}

public function down()
{
    Schema::table('erp_finance_fixed_asset_split', function (Blueprint $table) {
        $table->dropColumn('old_category_id');
    });

    Schema::table('erp_finance_fixed_asset_split_history', function (Blueprint $table) {
        $table->dropColumn('old_category_id');
    });

    Schema::table('erp_finance_fixed_asset_merger', function (Blueprint $table) {
        $table->dropColumn('old_category_id');
    });

    Schema::table('erp_finance_fixed_asset_merger_history', function (Blueprint $table) {
        $table->dropColumn('old_category_id');
    });
}

};
