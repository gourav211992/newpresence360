<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTransferCostCenterToErpFinanceFixedAssetIssueTransferTable extends Migration
{
    public function up()
    {
        Schema::table('erp_finance_fixed_asset_issue_transfer', function (Blueprint $table) {
            $table->string('transfer_cost_center')->nullable()->after('transfer_location');
            // Replace 'transfer_location' with the actual column name after which this should be added.
        });
    }

    public function down()
    {
        Schema::table('erp_finance_fixed_asset_issue_transfer', function (Blueprint $table) {
            $table->dropColumn('transfer_cost_center');
        });
    }
}
