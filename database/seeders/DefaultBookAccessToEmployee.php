<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeBookMapping;
use App\Models\Organization;
use App\Models\OrganizationMenu;
use App\Models\ServiceMenu;
use Exception;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultBookAccessToEmployee extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();
        try {
            $employees = Employee::select('id', 'organization_id') -> get();
            $serviceMenus = ServiceMenu::whereNotNull('erp_service_id') -> get();
            foreach ($employees as $employee) {
                $organization = Organization::find($employee -> organization_id);
                if ($organization) {
                    foreach ($serviceMenus as $serviceMenu) {
                        $existingServiceMenu = EmployeeBookMapping::where([
                            ['employee_id', $employee -> id],
                            ['organization_id', $organization -> id],
                            ['group_id', $organization -> group_id],
                            ['service_menu_id', $serviceMenu -> id]
                        ]) -> first();
                        if (!isset($existingServiceMenu)) {
                            EmployeeBookMapping::create([
                                'employee_id' => $employee -> id,
                                'organization_id' => $organization -> id,
                                'group_id' => $organization -> group_id,
                                'service_menu_id' => $serviceMenu -> id,
                                'erp_service_ids' => $serviceMenu -> erp_service_id,
                                'type' => 'all',
                                'book_ids' => null
                            ]);
                        }
                    }
                }
            }
            DB::commit();
        } catch(Exception $ex) {
            DB::rollBack();
            dd($ex->getMessage());
        }
    }
}
