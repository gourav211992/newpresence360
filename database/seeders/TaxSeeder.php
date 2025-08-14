<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $taxes = [
            ['name' => 'Zero Rate', 'value' => 0, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Three Percent', 'value' => 3, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Six Percent', 'value' => 6, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Nine Percent', 'value' => 9, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Twelve Percent', 'value' => 12, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Eighteen Percent', 'value' => 18, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Twenty-Eight Percent', 'value' => 28, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('erp_taxes')->insert($taxes);
    }
}
