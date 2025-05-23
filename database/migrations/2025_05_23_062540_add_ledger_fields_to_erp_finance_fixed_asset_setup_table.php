<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     public function up()
    {
        Schema::table('erp_finance_fixed_asset_setup', function (Blueprint $table) {
            $table->unsignedBigInteger('rev_ledger_id')->nullable()->after('dep_ledger_group_id');
            $table->unsignedBigInteger('rev_ledger_group_id')->nullable()->after('rev_ledger_id');

            $table->unsignedBigInteger('imp_ledger_id')->nullable()->after('rev_ledger_group_id');
            $table->unsignedBigInteger('imp_ledger_group_id')->nullable()->after('imp_ledger_id');

            $table->unsignedBigInteger('sales_ledger_id')->nullable()->after('imp_ledger_group_id');
            $table->unsignedBigInteger('sales_ledger_group_id')->nullable()->after('sales_ledger_id');
        });
        Schema::table('erp_finance_fixed_asset_revaluation_impairement', function (Blueprint $table) {
    $table->unsignedBigInteger('currency_id')->nullable()->after('category_id'); // replace 'some_existing_column' with an actual column name
});
    }

    public function down()
    {
        Schema::table('erp_finance_fixed_asset_setup', function (Blueprint $table) {
            $table->dropColumn([
                'rev_ledger_id',
                'rev_ledger_group_id',
                'imp_ledger_id',
                'imp_ledger_group_id',
                'sales_ledger_id',
                'sales_ledger_group_id',
            ]);
        });
        Schema::table('erp_finance_fixed_asset_revaluation_impairement', function (Blueprint $table) {
            $table->dropColumn('currency_id');
        });
    }
};
