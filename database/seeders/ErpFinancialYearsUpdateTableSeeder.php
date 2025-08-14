<?php

namespace Database\Seeders;

use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Models\ErpFinancialYear;
use App\Models\Organization;
use Exception;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Throwable;

class ErpFinancialYearsUpdateTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {
            $currentYear = Carbon::now()->year;

            $financialYears = ErpFinancialYear::get();
            foreach ($financialYears as $fy) {
                $startYear = Carbon::parse($fy->start_date)->year;

                $status = match (true) {
                    $startYear == $currentYear => 'current',
                    $startYear > $currentYear => 'next',
                    $startYear < $currentYear => 'prev',
                };


                ErpFinancialYear::where('id', $fy->id)
                    ->update([
                        'fy_status' => $status,
                    ]);

            }

            DB::commit();
            $this->command->info('FY status updated successfully.');
        } catch (Throwable $e) {
            DB::rollBack();
            $this->command->error('Failed to update FY status: ' . $e->getMessage());
        }

    }
}
