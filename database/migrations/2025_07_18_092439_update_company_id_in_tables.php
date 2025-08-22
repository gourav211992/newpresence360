<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $groupIds = DB::table('organization_groups')->pluck('id');

        foreach ($groupIds as $groupId) {
            $company = DB::table('organization_companies')
                ->where('group_id', $groupId)
                ->first();

            if ($company) {
                $companyId = $company->id;

                $tables = [
                    'erp_product_sections',
                    'erp_product_specifications',
                    'erp_categories',
                    'erp_taxes',
                    'erp_stores',
                    'erp_attribute_groups',
                    'erp_terms_and_conditions',
                    'erp_discount_master',
                    'erp_expense_master',
                    'erp_currency_exchanges',
                    'erp_payment_terms',
                    'erp_banks',
                    'erp_stations',
                    'erp_station_groups',
                    'erp_items',
                    'erp_vendors',
                    'erp_customers',
                    'erp_dynamic_fields',
                    'erp_inspection_checklists',
                ];

                foreach ($tables as $table) {
                    DB::table($table)
                        ->where('group_id', $groupId)
                        ->update(['company_id' => $companyId]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $groupIds = DB::table('organization_groups')->pluck('id');

        foreach ($groupIds as $groupId) {
            $company = DB::table('organization_companies')
                ->where('group_id', $groupId)
                ->first();

            if ($company) {
                $companyId = $company->id;

                $tables = [
                    'erp_product_sections',
                    'erp_product_specifications',
                    'erp_categories',
                    'erp_taxes',
                    'erp_stores',
                    'erp_attribute_groups',
                    'erp_terms_and_conditions',
                    'erp_discount_master',
                    'erp_expense_master',
                    'erp_currency_exchanges',
                    'erp_payment_terms',
                    'erp_banks',
                    'erp_stations',
                    'erp_station_groups',
                    'erp_items',
                    'erp_vendors',
                    'erp_customers',
                    'erp_dynamic_fields',
                    'erp_inspection_checklists',
                ];

                foreach ($tables as $table) {
                    DB::table($table)
                        ->where('group_id', $groupId)
                        ->where('company_id', $companyId)
                        ->update(['company_id' => null]);
                }
            }
        }
    }
};
