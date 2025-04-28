<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ErpStakeholderUserTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('erp_stakeholder_user_types')->insert([
            ['name' => 'Stakeholder'],
            ['name' => 'Investor'],
            ['name' => 'Government Relation'],
        ]);
    }
}
