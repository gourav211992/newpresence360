<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
    {
        Schema::table('erp_finance_fixed_asset_registration', function (Blueprint $table) {
            $table->unsignedBigInteger('it_category_id')->nullable()->after('category_id');
        });

        Schema::table('erp_finance_fixed_asset_registration_history', function (Blueprint $table) {
            $table->unsignedBigInteger('it_category_id')->nullable()->after('category_id');
        });
    }

    public function down()
    {
        Schema::table('erp_finance_fixed_asset_registration', function (Blueprint $table) {
            $table->dropColumn('it_category_id');
        });

        Schema::table('erp_finance_fixed_asset_registration_history', function (Blueprint $table) {
            $table->dropColumn('it_category_id');
        });
    }
};
