<?php 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCurrencyIdToErpFinanceFixedAssetRevHistoryTable extends Migration
{
    public function up()
    {
        Schema::table('erp_finance_fixed_asset_rev_history', function (Blueprint $table) {
            $table->unsignedBigInteger('currency_id')->nullable()->after('category_id'); // Replace 'your_column_name' with an actual column name
        });
    }

    public function down()
    {
        Schema::table('erp_finance_fixed_asset_rev_history', function (Blueprint $table) {
            $table->dropColumn('currency_id');
        });
    }
}
