<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('erp_finance_fixed_asset_registration', function (Blueprint $table) {
            $table->unsignedBigInteger('mrn_detail_id')->after('mrn_header_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('erp_finance_fixed_asset_registration', function (Blueprint $table) {
            $table->dropColumn('mrn_detail_id');
        });
    }
};
