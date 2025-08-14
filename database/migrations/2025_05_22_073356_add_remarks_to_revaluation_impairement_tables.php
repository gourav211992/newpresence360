<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRemarksToRevaluationImpairementTables extends Migration
{
    public function up()
    {
        Schema::table('erp_finance_fixed_asset_revaluation_impairement', function (Blueprint $table) {
            $table->text('remarks')->nullable()->after('document');
        });

        Schema::table('erp_finance_fixed_asset_revaluation_impairement_history', function (Blueprint $table) {
            $table->text('remarks')->nullable()->after('document');
        });
    }

    public function down()
    {
        Schema::table('erp_finance_fixed_asset_revaluation_impairement', function (Blueprint $table) {
            $table->dropColumn('remarks');
        });

        Schema::table('erp_finance_fixed_asset_revaluation_impairement_history', function (Blueprint $table) {
            $table->dropColumn('remarks');
        });
    }
}
