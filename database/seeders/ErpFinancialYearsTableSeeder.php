<?php

namespace Database\Seeders;

use App\Helpers\ConstantHelper;
use App\Models\ErpFinancialYear;
use App\Models\Organization;
use Exception;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ErpFinancialYearsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();
        try {
            ErpFinancialYear::truncate();
            $organizations = Organization::get();
            foreach ($organizations as $org) {
                ErpFinancialYear::create([
                    'group_id' => $org -> group_id,
                    'company_id' => $org ?-> company_id,
                    'organization_id' => $org -> id,
                    'alias' => "FY24",
                    'start_date' => "2024-04-01",
                    'end_date' => "2025-03-31",
                    'status' => ConstantHelper::ACTIVE
                ]);
            }
            DB::commit();
        } catch(Exception $ex) {
            DB::rollBack();
        }
        

    }
}
