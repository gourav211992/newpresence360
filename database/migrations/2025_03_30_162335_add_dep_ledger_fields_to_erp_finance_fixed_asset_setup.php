<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('erp_finance_fixed_asset_setup', function (Blueprint $table) {
            $table->unsignedBigInteger('dep_ledger_id')->nullable()->after('ledger_group_id');
            $table->unsignedBigInteger('dep_ledger_group_id')->nullable()->after('dep_ledger_id');
        });
    }

    public function down()
    {
        Schema::table('erp_finance_fixed_asset_setup', function (Blueprint $table) {
            $table->dropColumn(['dep_ledger_id', 'dep_ledger_group_id']);
        });
    }
};
