<?php

namespace Database\Seeders;

use App\Models\ErpInteractionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GovRelationManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = ['Call', 'Email','Meeting'];
        foreach ($types as $type) {
            ErpInteractionType::create(['name' => $type,'main'=>'gov-relation-management']);
        }
    }
}
