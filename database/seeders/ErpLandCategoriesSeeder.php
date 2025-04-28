<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ErpLandCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('erp_land_categories')->insert([
            [
                'id' => 1,
                'category_name' => 'Lease',
                'status' => 1,
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 2,
                'category_name' => 'Land-Lease',
                'status' => 1,
                'created_at' => null,
                'updated_at' => null
            ]
        ]);
    }
}
