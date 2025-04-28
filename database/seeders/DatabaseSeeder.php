<?php

namespace Database\Seeders;
use DB;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seeder to insert Default Ledger groups in database
        // if (DB::table('erp_groups')->where('name','Assets')->count() == 0) {  
        //     $this->call(GroupSeeder::class); 
        // } 

         $this->call([
          //   TaxSeeder::class,
          //  UnitSeeder::class,
            OrganizationTypeSeeder::class,
            //  PaymentTermsSeeder::class,
             CurrencySeeder::class,
              //CountrySeeder::class,
             // StateSeeder::class,
              //CitySeeder::class,
            SubTypeSeeder::class,
            // LocationSeeder::class,
            //ErpGroupMasterSeeder::class,
        ]);
       //$this->call(LoanOccupationSeeder::class);
    }
}
