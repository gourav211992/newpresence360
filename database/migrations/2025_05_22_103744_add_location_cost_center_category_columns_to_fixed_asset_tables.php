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
        $tables = [
            'erp_finance_fixed_asset_insurance',
            'erp_finance_fixed_asset_issue_transfer',
            'erp_finance_fixed_asset_maintenance',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                // Only for the issue_transfer table: drop the old `location` varchar column
                if ($tableName === 'erp_finance_fixed_asset_issue_transfer') {
                    $table->dropColumn('location');
                }

                // Now add the three new foreign-key id columns
                $table->unsignedBigInteger('category_id')->nullable()->after('id');
                $table->unsignedBigInteger('cost_center_id')->nullable()->after('category_id');
                $table->unsignedBigInteger('location_id')->nullable()->after('cost_center_id');
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'erp_finance_fixed_asset_insurance',
            'erp_finance_fixed_asset_issue_transfer',
            'erp_finance_fixed_asset_maintenance',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                // Drop the three new columns
                $table->dropColumn(['category_id', 'cost_center_id', 'location_id']);

                // If rolling back the issue_transfer table, you may want to restore the old `location` column:
                if ($tableName === 'erp_finance_fixed_asset_issue_transfer') {
                    $table->string('location')->after('status');
                }
            });
        }
    }
};
