<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('erp_finance_fixed_asset_setup', function (Blueprint $table) {
            $table->decimal('dep_percentage', 8, 2)->nullable()->after('salvage_percentage');
            $table->string('act_type')->nullable()->after('dep_percentage');
        });
    }

    public function down()
    {
        Schema::table('erp_finance_fixed_asset_setup', function (Blueprint $table) {
            $table->dropColumn(['dep_percentage', 'act_type']);
        });
    }
};
