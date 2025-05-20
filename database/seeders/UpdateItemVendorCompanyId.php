<?php

namespace Database\Seeders;

use App\Models\CustomerItem;
use App\Models\VendorItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UpdateItemVendorCompanyId extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vendorItems = VendorItem::all();
        foreach($vendorItems as $vendorItem) {
            $gId = $vendorItem?->item?->group_id ?? null;
            $orgId = $vendorItem?->item?->organization_id ?? null;
            $comId = $vendorItem?->item?->company_id ?? null;
            $vendorItem->group_id = $gId;
            $vendorItem->organization_id = $orgId;
            $vendorItem->company_id = $comId;
            $vendorItem->save();
        }

        $customerItems = CustomerItem::all();
        foreach($customerItems as $customerItem) {
            $gId = $customerItem?->item?->group_id ?? null;
            $orgId = $customerItem?->item?->organization_id ?? null;
            $comId = $customerItem?->item?->company_id ?? null;
            $customerItem->group_id = $gId;
            $customerItem->organization_id = $orgId;
            $customerItem->company_id = $comId;
            $customerItem->save();
        }
    }
}
