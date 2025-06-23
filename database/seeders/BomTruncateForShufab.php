<?php

namespace Database\Seeders;

use App\Models\Bom;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BomTruncateForShufab extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orgId = 81;
        $compId = 3;
        $groupId = 7;
        $boms = Bom::where('organization_id', $orgId)
            ->where('company_id', $compId)
            ->where('group_id', $groupId)
            ->get();
        foreach ($boms as $bom) {
            $bom->bomOverheadAllItems()->delete();
            $bom->bomNormAllItems()->delete();
            $bom->bomInstructions()->delete();
            $bom->bomAttributes()->delete();
            $bom->bomItemAttributes()->delete();
            $bom->bomItems()->delete();
            $bom->media()->delete();
            $bom->delete();
        }
    }
}
