<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('erp_finance_fixed_asset_sub', function (Blueprint $table) {
            $table->string('batch_number')->nullable()->after('sub_asset_code');
            $table->bigInteger('manufacturing_year')->nullable()->after('batch_number');
            $table->string('uid')->nullable()->after('manufacturing_year');
        });

        Schema::table('erp_finance_fixed_asset_sub_history', function (Blueprint $table) {
            $table->string('batch_number')->nullable()->after('sub_asset_code');
            $table->bigInteger('manufacturing_year')->nullable()->after('batch_number');
            $table->string('uid')->nullable()->after('manufacturing_year');
        });
    }

    public function down(): void
    {
        Schema::table('erp_finance_fixed_asset_sub', function (Blueprint $table) {
            $table->dropColumn(['batch_number', 'manufacturing_year', 'uid']);
        });

        Schema::table('erp_finance_fixed_asset_sub_history', function (Blueprint $table) {
            $table->dropColumn(['batch_number', 'manufacturing_year', 'uid']);
        });
    }
};
