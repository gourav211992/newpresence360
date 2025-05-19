<?php

namespace Database\Seeders;

use App\Models\EmployeeBookMapping;
use App\Models\OrganizationMenu;
use App\Models\PermissionMaster;
use App\Models\Service;
use App\Models\ServiceMenu;
use Exception;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RouteUpdateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        {
            DB::beginTransaction();
            try {
                EmployeeBookMapping::truncate();
                DB::commit();
            } catch(Exception $ex) {
                DB::rollBack();
                dd($ex -> getMessage());
            }
        }
    }
}
