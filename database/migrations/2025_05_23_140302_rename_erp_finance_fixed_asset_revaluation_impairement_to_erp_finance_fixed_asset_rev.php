<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class RenameErpFinanceFixedAssetRevaluationImpairementToErpFinanceFixedAssetRev extends Migration
{
    public function up()
    {
        Schema::rename('erp_finance_fixed_asset_revaluation_impairement', 'erp_finance_fixed_asset_rev');
        Schema::rename('erp_finance_fixed_asset_revaluation_impairement_history', 'erp_finance_fixed_asset_rev_history');
    }

    public function down()
    {
        Schema::rename('erp_finance_fixed_asset_rev', 'erp_finance_fixed_asset_revaluation_impairement');
         Schema::rename('erp_finance_fixed_asset_rev_history', 'erp_finance_fixed_asset_revaluation_impairement_history');
    }
}
