<?php

namespace Database\Seeders;

use App\Models\ErpInteractionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PublicOutreachAndCommunicationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = ['Press Release', 'Press Conference', 'Public Semina'];
        foreach ($types as $type) {
            ErpInteractionType::create(['name' => $type,'main'=>'public-outreach']);
        }
    }
}
