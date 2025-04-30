<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Update old NULLs first
        DB::table('erp_finance_fixed_asset_depreciation')
            ->whereNull('revision_number')
            ->update(['revision_number' => 0]);

        // Change the column type
        Schema::table('erp_finance_fixed_asset_depreciation', function (Blueprint $table) {
            $table->integer('revision_number')->default(0)->change();
        });
    }

    public function down()
    {
        Schema::table('erp_finance_fixed_asset_depreciation', function (Blueprint $table) {
            $table->string('revision_number')->nullable()->default(null)->change();
        });
    }
};
