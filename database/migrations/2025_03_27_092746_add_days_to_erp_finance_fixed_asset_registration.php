<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('erp_finance_fixed_asset_registration', function (Blueprint $table) {
            $table->integer('days')->default(0)->after('total_depreciation'); // Replace 'existing_column' with the appropriate column name
        });
    }

    public function down()
    {
        Schema::table('erp_finance_fixed_asset_registration', function (Blueprint $table) {
            $table->dropColumn('days');
        });
    }
};


