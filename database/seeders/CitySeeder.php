<?php

namespace Database\Seeders;
use App\Models\City;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = base_path() . '/database/sql/cities.sql';
        $sql = file_get_contents($path);
        DB::unprepared($sql);
    }
}
