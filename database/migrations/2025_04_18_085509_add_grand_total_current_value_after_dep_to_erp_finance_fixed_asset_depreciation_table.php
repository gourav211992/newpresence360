<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('erp_finance_fixed_asset_depreciation', function (Blueprint $table) {
            $table->decimal('grand_total_current_value_after_dep', 20, 2)->nullable()->after('grand_total_current_value');
        });
    }
    
    public function down()
    {
        Schema::table('erp_finance_fixed_asset_depreciation', function (Blueprint $table) {
            $table->dropColumn('grand_total_current_value_after_dep');
        });
    }
    
};
