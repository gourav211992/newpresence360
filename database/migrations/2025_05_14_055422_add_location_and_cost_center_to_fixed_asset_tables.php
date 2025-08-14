<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
            Schema::table('erp_finance_fixed_asset_depreciation', function (Blueprint $table) {
                $table->unsignedBigInteger('location_id')->nullable()->after('doc_no');
                $table->unsignedBigInteger('cost_center_id')->nullable()->after('location_id');
            });
        
    }

    public function down()
    {
            Schema::table('erp_finance_fixed_asset_depreciation', function (Blueprint $table) {
                $table->dropColumn(['location_id', 'cost_center_id']);
            });
        
    }

};
