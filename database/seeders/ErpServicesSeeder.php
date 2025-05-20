<?php

namespace Database\Seeders;

use App\Helpers\ConstantHelper;
use App\Models\Book;
use App\Models\ErpOrganizationService;
use App\Models\ErpService;
use App\Models\Organization;
use App\Models\OrganizationService;
use App\Models\Service;
use DB;
use Exception;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ErpServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();
        try {
            //First add org_service_id to Books Table 
            $allBooks = Book::all();
            foreach ($allBooks as $currentBook) {
                $org_service_id = $currentBook ?-> bookType ?-> service_id;
                $currentBook -> org_service_id = $org_service_id;
                $currentBook -> save();
            }
            //Erp Service -> Replicate services table data
            $serviceIds = DB::table('organization_services')->where('flag', 1) -> get() -> pluck('service_id') -> toArray();
            $services = DB::table('services')->whereIn('id', $serviceIds) -> get();
            foreach ($services as $service) {
                ErpService::create([
                    'name' => $service -> name,
                    'alias' => $service -> alias,
                    'status' => $service -> status,
                ]);
            }

            //Erp Organization Service -> Assign the created services to all organization at group level
            $organizations = Organization::where('status', ConstantHelper::ACTIVE) -> get();
            foreach ($organizations as $organization) {
                $erpServices = ErpService::where('status', ConstantHelper::ACTIVE) -> get();
                foreach ($erpServices as $erpService) {
                    ErpOrganizationService::create([
                        'group_id' => $organization -> group_id,
                        'company_id' => null,
                        'organization_id' => null,
                        'service_id' => $erpService -> id,
                        'name' => $erpService -> name,
                        'alias' => $erpService -> alias,
                        'status' => $erpService -> status
                    ]);
                }
            }
            // Update Book service mapping and add service id from erp_services table
            $books = Book::all();
            foreach ($books as $book) {
                $orgService = DB::table('organization_services') -> where('id', $book -> org_service_id) -> first();
                if (isset($orgService))
                {
                    $erpOrganizationService = ErpOrganizationService::where('alias', $orgService ?-> alias) -> first();
                    if (isset($erpOrganizationService)) {
                        $book-> org_service_id = $erpOrganizationService -> id;
                        $book-> service_id = $erpOrganizationService -> service ?-> id;
                        $book-> save();
                    }
                }
                
            }
            DB::commit();
        } catch(Exception $ex) {
            DB::rollBack();
            dd($ex);
        }
    }
}
