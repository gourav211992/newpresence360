<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $currencies = [
            ['name' => 'US Dollar', 'short_name' => 'USD', 'symbol' => '$', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Euro', 'short_name' => 'EUR', 'symbol' => '€', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'British Pound', 'short_name' => 'GBP', 'symbol' => '£', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Indian Rupee', 'short_name' => 'INR', 'symbol' => '₹', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Japanese Yen', 'short_name' => 'JPY', 'symbol' => '¥', 'status' => 'inactive', 'created_at' => $now, 'updated_at' => $now],
        ];
        DB::table('currency')->insert($currencies);
    }
}
