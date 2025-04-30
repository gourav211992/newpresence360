<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
        public function up()
        {
            Schema::table('erp_finance_fixed_asset_depreciation', function (Blueprint $table) {
                $table->json('asset_details')->nullable()->after('assets');
            });
        }
    
        public function down()
        {
            Schema::table('erp_finance_fixed_asset_depreciation', function (Blueprint $table) {
                $table->dropColumn('asset_details');
            });
        }
    
    
};
