<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupSeeder extends Seeder
{
    public function run()  
    {  
        $data = [  
            [
                "name" => "Assets",
                'children' => [  
                    ['name' => 'Branch / Divisions'], 
                    ['name' => 'Current Assets'], 
                    ['name' => 'Bank Accounts'], 
                    ['name' => 'Cash-in-Hand'], 
                    ['name' => 'Deposits (Asset)'], 
                    ['name' => 'Loans & Advances (Asset)'], 
                    ['name' => 'Stock-in-Hand'], 
                    ['name' => 'Sundry Debtors'], 
                    ['name' => 'Fixed Assets'], 
                    ['name' => 'Investments'], 
                    ['name' => 'Misc. Expenses (ASSET)'], 
                    ['name' => 'Suspense A/c'], 
                ]
            ],  
            [
                "name" => "Liabilities",
                'children' => [  
                    ['name' => 'Capital Account'],  
                    ['name' => 'Reserves & Surplus'],  
                    ['name' => 'Current Liabilities'],  
                    ['name' => 'Duties & Taxes'],  
                    ['name' => 'Provisions'],  
                    ['name' => 'Sundry Creditors'],  
                    ['name' => 'Loans (Liability)'],  
                    ['name' => 'Bank OD A/c'],  
                    ['name' => 'Bank OCC A/c'],  
                    ['name' => 'Secured Loans'],  
                    ['name' => 'Unsecured Loans'],  
                    ['name' => 'Retained Earnings'],  
                ]
            ],  
            [
                "name" => "Expenses",
                'children' => [  
                    ['name' => 'Direct Expenses'], 
                    ['name' => 'Indirect Expenses'], 
                    ['name' => 'Purchase Accounts'], 
                ]
            ],  
            [
                "name" => "Incomes",
                'children' => [  
                    ['name' => 'Direct Incomes'],  
                    ['name' => 'Indirect Incomes'],  
                    ['name' => 'Sales Accounts'],  
                ]
            ]
        ];  

        // Step 1: Insert parent records and store their IDs  
        $parentIds = [];  
        foreach ($data as $parent) {  
            $parentId = DB::table('erp_groups')->insertGetId([  
                'name' => $parent['name'],  
                'parent_group_id' => null,  
                'status' => 'active',  
                'created_at' => now(),  
                'updated_at' => now(),  
            ]);  
            $parentIds[$parent['name']] = $parentId; // Store the parent ID  
        }  

        // Step 2: Insert child records using the stored parent IDs  
        foreach ($data as $parent) {  
            foreach ($parent['children'] as $child) {  
                DB::table('erp_groups')->insert([  
                    'name' => $child['name'],  
                    'parent_group_id' => $parentIds[$parent['name']], // Reference the parent ID  
                    'status' => 'active',  
                    'created_at' => now(),  
                    'updated_at' => now(),  
                ]);  
            }  
        }  
    }
}
