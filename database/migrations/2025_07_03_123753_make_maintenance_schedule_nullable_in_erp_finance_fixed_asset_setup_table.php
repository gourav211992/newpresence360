<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     public function up()
    {
        Schema::table('erp_finance_fixed_asset_setup', function (Blueprint $table) {
            $table->string('maintenance_schedule')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('erp_finance_fixed_asset_setup', function (Blueprint $table) {
            $table->string('maintenance_schedule')->nullable(false)->change();
        });
    }
};
