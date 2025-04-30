<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('erp_finance_fixed_asset_depreciation', function (Blueprint $table) {
            $table->unsignedBigInteger('currency_id')->nullable()->after('doc_no');
        });
    }

    public function down()
    {
        Schema::table('erp_finance_fixed_asset_depreciation', function (Blueprint $table) {
            $table->dropColumn('currency_id');
        });
    }
};
