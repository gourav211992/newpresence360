<?php

namespace Database\Seeders;

use App\Models\ErpInteractionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EngagementTrackingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = ['Highly Engaged', 'Moderately Engaged', 'Occasionally', 'Engaged','Not Engaged'];
        foreach ($types as $type) {
            ErpInteractionType::create(['name' => $type,'main'=>'engagement-tracking']);
        }
    }
}
