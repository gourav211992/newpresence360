<?php

namespace Database\Seeders;

use App\Models\CustomerItem;
use App\Models\ErpItemAttribute;
use App\Models\VendorItem;
use DB;
use Exception;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RemoveRedundantItemRelatedData extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();
        try {
            CustomerItem::whereNull('customer_id') -> forceDelete();
            VendorItem::whereNull('vendor_id') -> forceDelete();
            ErpItemAttribute::whereDoesntHave('group') -> forceDelete();
            DB::commit();
        } catch(Exception $ex) {
            DB::rollBack();
            dd($ex -> getMessage());
        }
    }
}
