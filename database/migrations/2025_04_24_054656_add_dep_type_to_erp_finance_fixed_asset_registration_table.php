<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('erp_finance_fixed_asset_registration', function (Blueprint $table) {
        $table->string('dep_type')->nullable()->after('depreciation_percentage_year'); // Use `nullable` if it's optional
    });
}

public function down()
{
    Schema::table('erp_finance_fixed_asset_registration', function (Blueprint $table) {
        $table->dropColumn('dep_type');
    });
}
};
