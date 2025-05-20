<?php

namespace Database\Seeders;

use App\Models\ErpInteractionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RelationManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = ['Call', 'Email'];
        foreach ($types as $type) {
            ErpInteractionType::create(['name' => $type,'main'=>'relation-management']);
        }
    }
}
