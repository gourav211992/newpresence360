<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $units = [
            ['name' => 'KG','description'=> 'Kilogram','status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'L', 'description'=> 'Liter', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'MT', 'description'=> 'Meter', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'P', 'description'=> 'Piece', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('erp_units')->insert($units);
    }
}
