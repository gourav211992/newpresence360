<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add to erp_finance_fixed_asset_sub
        Schema::table('erp_finance_fixed_asset_sub', function (Blueprint $table) {
            $table->date('capitalize_date')->nullable()->after('total_depreciation');
            $table->date('last_dep_date')->nullable()->after('capitalize_date');
            $table->date('expiry_date')->nullable()->after('last_dep_date');
        });

        // Add to erp_finance_fixed_asset_sub_history
        Schema::table('erp_finance_fixed_asset_sub_history', function (Blueprint $table) {
            $table->date('capitalize_date')->nullable()->after('total_depreciation');
              $table->date('last_dep_date')->nullable()->after('capitalize_date');
           $table->date('expiry_date')->nullable()->after('last_dep_date');
        });
    }

    public function down(): void
    {
        Schema::table('erp_finance_fixed_asset_sub', function (Blueprint $table) {
            $table->dropColumn(['capitalize_date', 'last_dep_date', 'expiry_date']);
        });

        Schema::table('erp_finance_fixed_asset_sub_history', function (Blueprint $table) {
            $table->dropColumn(['capitalize_date', 'last_dep_date', 'expiry_date']);
        });
    }
};
