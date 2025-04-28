<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class LoanFinancialAccountsSeeder extends Seeder
{
    public function run()
    {
        DB::table('erp_loan_financial_accounts')->insert([
            'id' => 1,
            'pro_ledger_id' => 65,
            'pro_ledger_group_id' => 20,
            'dis_ledger_id' => 61,
            'dis_ledger_group_id' => 10,
            'int_ledger_id' => 59,
            'int_ledger_group_id' => 15,
            'wri_ledger_id' => 46,
            'wri_ledger_group_id' => 24,
            'created_at' => Carbon::parse('2024-12-26 08:09:34'),
            'updated_at' => Carbon::parse('2025-01-21 06:42:10'),
        ]);
    }
}
