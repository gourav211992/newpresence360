<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;

class LoanOccupationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach(['Salaried', 'Self Employed', 'Business'] as $key => $val){
            DB::table('erp_loan_occupations')->insert([
                'name' => $val
            ]);
        }
    }
}
