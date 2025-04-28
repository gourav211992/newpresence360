<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\OrganizationService;
use Exception;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ErpServicesDuplicateRemovalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();
        try {
            // Find all duplicate OrganizationService entries by alias and group_id
            $duplicates = OrganizationService::select('id', 'alias', 'group_id')
                ->groupBy('alias', 'group_id')
                ->havingRaw('COUNT(*) > 1')
                ->get();
        
            $deletableOrgServiceIds = [];
        
            // Loop through each duplicate entry group
            foreach ($duplicates as $duplicate) {
                // Get the first record to keep
                $original = OrganizationService::where('alias', $duplicate->alias)
                    ->where('group_id', $duplicate->group_id)
                    ->orderBy('id')
                    ->first();
        
                // Get all duplicates except the first record
                $duplicateOrgServices = OrganizationService::where('alias', $duplicate->alias)
                    ->where('group_id', $duplicate->group_id)
                    ->where('id', '!=', $original->id) // Exclude the original
                    ->pluck('id')
                    ->toArray();
        
                // Collect IDs of records to delete
                $deletableOrgServiceIds = array_merge($deletableOrgServiceIds, $duplicateOrgServices);
        
                // Update `org_service_id` in Book table to point to the original
                Book::whereIn('org_service_id', $duplicateOrgServices)
                    ->update(['org_service_id' => $original->id]);
            }
        
            // Delete all duplicate OrganizationService records
            OrganizationService::whereIn('id', $deletableOrgServiceIds)->delete();
        
            DB::commit();
        
        } catch (Exception $ex) {
            DB::rollBack(); // Ensure rollback on exception
            dd($ex->getMessage());
        }
    }
}
