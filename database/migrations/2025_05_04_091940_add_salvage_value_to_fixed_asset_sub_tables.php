<?php 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSalvageValueToFixedAssetSubTables extends Migration
{
    public function up()
    {
        Schema::table('erp_finance_fixed_asset_sub', function (Blueprint $table) {
            $table->decimal('salvage_value', 15, 2)->nullable()->after('current_value_after_dep'); // replace 'current_value_after_dep' with actual one
        });

        Schema::table('erp_finance_fixed_asset_sub_history', function (Blueprint $table) {
            $table->decimal('salvage_value', 15, 2)->nullable()->after('current_value_after_dep'); // same here
        });
    }

    public function down()
    {
        Schema::table('erp_finance_fixed_asset_sub', function (Blueprint $table) {
            $table->dropColumn('salvage_value');
        });

        Schema::table('erp_finance_fixed_asset_sub_history', function (Blueprint $table) {
            $table->dropColumn('salvage_value');
        });
    }
}
