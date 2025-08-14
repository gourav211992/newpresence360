<?php

namespace Database\Seeders;

use App\Models\ErpOrganizationService;
use App\Models\ErpService;
use App\Models\OrganizationService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ErpOrganizationServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $erpOrganizationServices = [];
        OrganizationService::where('flag', 1) -> chunkById(250, function ($orgServices) use($erpOrganizationServices) {
            foreach ($orgServices as $orgService) {
                $erpService = ErpService::find($orgService -> alias);
                if (isset($erpService)) {
                    array_push($erpOrganizationServices, [
                        'group_id' => $orgService -> group_id,
                        'company_id' => $orgService -> company_id,
                        'organization_id' => $orgService -> organization_id,
                        'service_id' => $erpService -> id,
                        'name' => $orgService -> name,
                        'alias' => $orgService -> alias,
                        'icon' => $orgService -> icon,
                        'status' => $orgService -> status
                    ]);
                }
            }
        });
        ErpOrganizationService::insert($erpOrganizationServices);
    }
}
