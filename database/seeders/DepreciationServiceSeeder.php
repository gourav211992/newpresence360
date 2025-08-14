<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepreciationServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Service::insert([
            'alias' => 'depreciation',
            'name'  => 'Depreciation',
        ]);
    }
}
