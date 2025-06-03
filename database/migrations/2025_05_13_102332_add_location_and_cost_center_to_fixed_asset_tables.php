<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $tables = [
            'erp_finance_fixed_asset_registration',
            'erp_finance_fixed_asset_registration_history',
            'erp_finance_fixed_asset_split',
            'erp_finance_fixed_asset_split_history',
            'erp_finance_fixed_asset_merger',
            'erp_finance_fixed_asset_merger_history',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->unsignedBigInteger('location_id')->nullable()->after('doc_no');
                $table->unsignedBigInteger('cost_center_id')->nullable()->after('location_id');
            });
        }
    }

    public function down()
    {
        $tables = [
            'erp_finance_fixed_asset_registration',
            'erp_finance_fixed_asset_registration_history',
            'erp_finance_fixed_asset_split',
            'erp_finance_fixed_asset_split_history',
            'erp_finance_fixed_asset_merger',
            'erp_finance_fixed_asset_merger_history',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn(['location_id', 'cost_center_id']);
            });
        }
    }

};
