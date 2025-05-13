<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('erp_asset_category', function (Blueprint $table) {
            $table->unsignedBigInteger('group_id')->nullable()->change();
            $table->unsignedBigInteger('company_id')->nullable()->change();
            $table->unsignedBigInteger('organization_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('erp_asset_category', function (Blueprint $table) {
            $table->unsignedBigInteger('group_id')->nullable(false)->change();
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
            $table->unsignedBigInteger('organization_id')->nullable(false)->change();
        });
    }
};
