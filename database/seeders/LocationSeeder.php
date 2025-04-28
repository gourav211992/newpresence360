<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;

class LocationSeeder extends Seeder
{
    public function run()
    {
        // Create 50 dummy location records
        Location::factory()->count(50)->create();
    }
}

