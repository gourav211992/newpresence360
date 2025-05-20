<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('
            ALTER TABLE `erp_expense_master`
                MODIFY `expense_ledger_id` BIGINT UNSIGNED NULL,
                MODIFY `service_provider_ledger_id` BIGINT UNSIGNED NULL;
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            ALTER TABLE `erp_expense_master`
                MODIFY `expense_ledger_id` BIGINT UNSIGNED NOT NULL,
                MODIFY `service_provider_ledger_id` BIGINT UNSIGNED NOT NULL;
        ');
    }
};
