<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('erp_finance_fixed_asset_sub', function (Blueprint $table) {
            $table->unsignedBigInteger('location_id')->nullable()->after('parent_id');
            $table->unsignedBigInteger('cost_center_id')->nullable()->after('location_id');
        });

        Schema::table('erp_finance_fixed_asset_sub_history', function (Blueprint $table) {
            $table->unsignedBigInteger('location_id')->nullable()->after('parent_id');
            $table->unsignedBigInteger('cost_center_id')->nullable()->after('location_id');
        });
    
    }

    public function down(): void
    {
        Schema::table('erp_finance_fixed_asset_sub', function (Blueprint $table) {
            $table->dropColumn(['location_id', 'cost_center_id']);
        });

        Schema::table('erp_finance_fixed_asset_sub_history', function (Blueprint $table) {
            $table->dropColumn(['location_id', 'cost_center_id']);
        });
    }
};
