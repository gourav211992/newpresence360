<?php

namespace Database\Seeders;

use App\Models\BomDetail;
use App\Models\BomDetailHistory;
use App\Models\ProductSectionDetail;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UpdateSectionIdInBomDetail extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bomDetails = BomDetail::whereNotNull('sub_section_id')->get();
        foreach($bomDetails as $bomDetail) {
            $subSection = ProductSectionDetail::where("id", $bomDetail->sub_section_id)->first();
            if($subSection) {
                $bomDetail->section_id = $subSection->section_id;
                $bomDetail->save();
            }
        }

        $bomDetailHistory = BomDetailHistory::whereNotNull('sub_section_id')->get();
        foreach($bomDetailHistory as $bomDetailHis) {
            $subSection = ProductSectionDetail::where("id", $bomDetailHis->sub_section_id)->first();
            if($subSection) {
                $bomDetailHis->section_id = $subSection->section_id;
                $bomDetailHis->save();
            }
        }

        echo "Done!";

    }
}
