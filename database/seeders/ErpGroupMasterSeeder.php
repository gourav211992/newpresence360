<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ErpGroupMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $erpGroupMasters = [
            ['group_id' => 1, 'master_name' => 'item', 'alias' => 'item_alias', 'sharing_policy' => 'global', 'default' => null, 'created_at' => $now, 'updated_at' => $now],
            ['group_id' => 1, 'master_name' => 'customer', 'alias' => 'customer_alias', 'sharing_policy' => 'company',  'default' => null,'created_at' => $now, 'updated_at' => $now],
            ['group_id' => 1, 'master_name' => 'vendor', 'alias' => 'vendor_alias', 'sharing_policy' => 'local', 'default' => null, 'created_at' => $now, 'updated_at' => $now],
            ['group_id' => 1, 'master_name' => 'ledger', 'alias' => 'ledger_alias', 'sharing_policy' => 'hybrid', 'default' => 'company', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('erp_group_master_sharing')->insert($erpGroupMasters);
    }
}
