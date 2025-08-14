<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentTermsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $paymentTerms = [
            ['name' => 'Net 30', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Net 60', 'status' => 'inactive', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Due on Receipt', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'End of Month', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Cash on Delivery', 'status' => 'inactive', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('erp_payment_terms')->insert($paymentTerms);
    }
}
