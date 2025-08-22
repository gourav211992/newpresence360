<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (['erp_mrn_asset_details', 'erp_mrn_asset_details_history'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->string('brand_name')->nullable()->after('capitalization_date');
                $table->string('model_no')->nullable()->after('brand_name');
            });
        }
    }

    public function down(): void
    {
        foreach (['erp_mrn_asset_details', 'erp_mrn_asset_details_history'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->dropColumn(['brand_name', 'model_no']);
            });
        }
    }
};
