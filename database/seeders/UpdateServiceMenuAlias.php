<?php

namespace Database\Seeders;

use App\Models\OrganizationMenu;
use App\Models\PermissionMaster;
use App\Models\Service;
use App\Models\ServiceMenu;
use DB;
use Exception;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UpdateServiceMenuAlias extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();
        try {
            // $serviceMenus = ServiceMenu::where('alias', 'sales-order_so') -> get();
    
            // foreach ($serviceMenus as $serviceMenu) {
            //     $serviceMenu -> alias = "sales-order";
            //     $serviceMenu -> save();
            // }
            // $serviceMenus = ServiceMenu::where('alias', 'sales-order_sq') -> get();
    
            // foreach ($serviceMenus as $serviceMenu) {
            //     $serviceMenu -> alias = "sales-quotation";
            //     $serviceMenu -> save();
            // }
    
            // $organizationMenus = OrganizationMenu::where('alias', 'sales-order_sq') -> get();
    
            // foreach ($organizationMenus as $organizationMenu) {
            //     $organizationMenu -> alias = "sales-quotation";
            //     $organizationMenu -> save();
            // }
            // $organizationMenus = OrganizationMenu::where('alias', 'sales-order_so') -> get();
    
            // foreach ($organizationMenus as $organizationMenu) {
            //     $organizationMenu -> alias = "sales-order";
            //     $organizationMenu -> save();
            // }
    
            // $permissionMasters = PermissionMaster::where('alias', 'menu.sales-order_sq') -> get();
    
            // foreach ($permissionMasters as $permissionMaster) {
            //     $permissionMaster -> alias = "menu.sales-quotation";
            //     $permissionMaster -> save();
            // }

            // $permissionMasters = PermissionMaster::where('alias', 'menu.sales-order_so') -> get();
    
            // foreach ($permissionMasters as $permissionMaster) {
            //     $permissionMaster -> alias = "menu.sales-order";
            //     $permissionMaster -> save();
            // }
            $vendorService = Service::where('alias', 'vendor') -> first();
            if (isset($vendorService)) {
                $vendorService -> alias = "vendors";
                $vendorService -> save();
            }
            DB::commit();
        } catch(Exception $ex) {
            DB::rollBack();
            dd($ex -> getMessage());
        }
    }
}
