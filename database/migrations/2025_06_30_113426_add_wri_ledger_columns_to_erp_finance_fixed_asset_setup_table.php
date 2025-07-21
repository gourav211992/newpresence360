<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('erp_finance_fixed_asset_setup', function (Blueprint $table) {
        $table->integer('wri_ledger_group_id')->nullable()->after('rev_ledger_group_id');
        $table->integer('wri_ledger_id')->nullable()->after('wri_ledger_group_id');
    });
}

public function down()
{
    Schema::table('erp_finance_fixed_asset_setup', function (Blueprint $table) {
        $table->dropColumn(['wri_ledger_group_id', 'wri_ledger_id']);
    });
}
};
