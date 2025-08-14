<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; // You can use this if you want more precise control

class ErpDocumentsTableSeeder extends Seeder
{
    public function run()
    {
        $documents = [
            ['name' => 'sale-deed', 'service' => 'land', 'group_id' => 1, 'organization_id' => 1, 'company_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'title-deed', 'service' => 'land', 'group_id' => 1, 'organization_id' => 1, 'company_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'mother-deed', 'service' => 'land', 'group_id' => 1, 'organization_id' => 1, 'company_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'encumbrance-certificate', 'service' => 'land', 'group_id' => 1, 'organization_id' => 1, 'company_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'khata-certificate', 'service' => 'land', 'group_id' => 1, 'organization_id' => 1, 'company_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'khata-extract', 'service' => 'land', 'group_id' => 1, 'organization_id' => 1, 'company_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'mutation-certificate', 'service' => 'land', 'group_id' => 1, 'organization_id' => 1, 'company_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'patta-chitta', 'service' => 'land', 'group_id' => 1, 'organization_id' => 1, 'company_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'property-tax-receipt', 'service' => 'land', 'group_id' => 1, 'organization_id' => 1, 'company_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'possession-certificate', 'service' => 'land', 'group_id' => 1, 'organization_id' => 1, 'company_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'land-conversion-certificate', 'service' => 'land', 'group_id' => 1, 'organization_id' => 1, 'company_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'non-agricultural-land-certificate', 'service' => 'land', 'group_id' => 1, 'organization_id' => 1, 'company_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'zoning-certificate', 'service' => 'land', 'group_id' => 1, 'organization_id' => 1, 'company_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'building-plan-approval', 'service' => 'land', 'group_id' => 1, 'organization_id' => 1, 'company_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'occupancy-certificate', 'service' => 'land', 'group_id' => 1, 'organization_id' => 1, 'company_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'power-of-attorney', 'service' => 'land', 'group_id' => 1, 'organization_id' => 1, 'company_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'registered-will', 'service' => 'land', 'group_id' => 1, 'organization_id' => 1, 'company_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'gift-deed', 'service' => 'land', 'group_id' => 1, 'organization_id' => 1, 'company_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'revenue-records', 'service' => 'land', 'group_id' => 1, 'organization_id' => 1, 'company_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'land-use-certificate', 'service' => 'land', 'group_id' => 1, 'organization_id' => 1, 'company_id' => 1, 'created_at' => now(), 'updated_at' => now()],
        ];

        // Insert documents into the database
        DB::table('erp_documents')->insert($documents);
    }
}
