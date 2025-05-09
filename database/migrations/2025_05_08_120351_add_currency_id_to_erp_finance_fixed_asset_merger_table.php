<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\FixedAssetMerger;
use App\Helpers\Helper;
return new class extends Migration
{
    public function up()
    {
        Schema::table('erp_finance_fixed_asset_merger', function (Blueprint $table) {
            $table->unsignedBigInteger('currency_id')->nullable()->after('id');
        });
        if (class_exists(FixedAssetMerger::class)) {
            FixedAssetMerger::query()->update(['currency_id' =>1]); // Set default currency_id
        }
    }

    public function down()
    {
        Schema::table('erp_finance_fixed_asset_merger', function (Blueprint $table) {
            $table->dropColumn('currency_id');
        });
    }};
