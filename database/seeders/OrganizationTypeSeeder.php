<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrganizationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $organizationTypes = [
            ['name' => 'Private Ltd', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Public Ltd', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Partnership Firm', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Society', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Trust', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'HUF', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Proprietor', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Others', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('erp_organization_types')->insert($organizationTypes);
    }
}
