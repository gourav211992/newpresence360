<?php

namespace Database\Seeders;

use App\Models\ErpInteractionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ErpInteractionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = ['Email', 'Meeting', 'Phone'];
        foreach ($types as $type) {
            ErpInteractionType::create(['name' => $type,'main'=>'interaction']);
        }
        $types1 = ['Suggestion', 'Complaint'];

        foreach ($types1 as $type) {
            ErpInteractionType::create(['name' => $type,'main'=>'feedback']);
        }
    }
}
