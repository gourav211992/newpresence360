<?php

namespace Database\Seeders;

use App\Models\Hsn;
use App\Models\HsnTaxPattern;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HsnFromDateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();
        try {
            HsnTaxPattern::withTrashed() -> where('from_date', NULL) -> update([
                'from_date' => "2024-04-01"
            ]);
            DB::commit();
        } catch(Exception $ex) {
            DB::rollBack();
            dd($ex -> getMessage());
        }
    }
}
