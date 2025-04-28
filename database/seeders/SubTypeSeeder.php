<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Helpers\ConstantHelper;

class SubTypeSeeder extends Seeder
{
    public function run()
    {
        $subTypes = [
            ['name' => 'Raw Material', 'status' => ConstantHelper::ACTIVE],
            ['name' => 'WIP/Semi Finished', 'status' => ConstantHelper::ACTIVE],
            ['name' => 'Finished Goods', 'status' => ConstantHelper::ACTIVE],
            ['name' => 'Traded Item', 'status' => ConstantHelper::ACTIVE],
            ['name' => 'Asset', 'status' => ConstantHelper::ACTIVE],
            ['name' => 'Expense', 'status' => ConstantHelper::ACTIVE],
        ];

        DB::table('erp_sub_types')->insert($subTypes);
    }
}
