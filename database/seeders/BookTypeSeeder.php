<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BookTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()  
    {  
        $data = [  
            [
                "name" => "Journal",
                'service_id' => 1,
                'status' => 'Active'
            ],
            [
                "name" => "Payment",
                'service_id' => 1,
                'status' => 'Active'
            ],
            [
                "name" => "Receipt",
                'service_id' => 1,
                'status' => 'Active'
            ],
            [
                "name" => "Contra",
                'service_id' => 1,
                'status' => 'Active'
            ],
            [
                "name" => "Sales",
                'service_id' => 1,
                'status' => 'Active'
            ],
            [
                "name" => "Purchase",
                'service_id' => 1,
                'status' => 'Active'
            ],
            [
                "name" => "Debit Note",
                'service_id' => 1,
                'status' => 'Active'
            ],
            [
                "name" => "Credit Note",
                'service_id' => 1,
                'status' => 'Active'
            ]
        ];  

        //Insert Default BookType records 
        foreach ($data as $parent) {  
            DB ::table('erp_book_types')->insert($parent);  
        } 
    }
}
