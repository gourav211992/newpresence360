<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
       public function up(): void
    {
        Schema::table('erp_finance_fixed_asset_sub', function (Blueprint $table) {
            $table->dropUnique(['sub_asset_code']);
        });
    }

    public function down(): void
    {
        Schema::table('erp_finance_fixed_asset_sub', function (Blueprint $table) {
            $table->unique('sub_asset_code');
        });
    }
};
