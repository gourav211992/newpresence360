<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $tables = [
            'erp_so_items',
            'erp_invoice_items',
            'erp_sale_return_items',
            'erp_mi_items',
            'erp_pslip_items',
        ];

        foreach ($tables as $table) {
            // Get all columns in the table that contain 'qty' or are named 'rate'
            $columns = DB::select("
                SELECT COLUMN_NAME 
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_NAME = '{$table}' 
                AND TABLE_SCHEMA = DATABASE()
                AND (COLUMN_NAME LIKE '%qty%' OR COLUMN_NAME = 'rate')
            ");

            // Alter each matching column to DECIMAL(20,6)
            foreach ($columns as $column) {
                DB::statement("
                    ALTER TABLE `{$table}` 
                    MODIFY COLUMN `{$column->COLUMN_NAME}` DECIMAL(20,6)
                ");
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'erp_so_items',
            'erp_invoice_items',
            'erp_sale_return_items',
            'erp_mi_items',
            'erp_pslip_items',
        ];

        foreach ($tables as $table) {
            // Get all columns in the table that contain 'qty' or are named 'rate'
            $columns = DB::select("
                SELECT COLUMN_NAME 
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_NAME = '{$table}' 
                AND TABLE_SCHEMA = DATABASE()
                AND (COLUMN_NAME LIKE '%qty%' OR COLUMN_NAME = 'rate')
            ");

            // Revert each column back to DECIMAL(15,2)
            foreach ($columns as $column) {
                DB::statement("
                    ALTER TABLE `{$table}` 
                    MODIFY COLUMN `{$column->COLUMN_NAME}` DECIMAL(15,2)
                ");
            }
        }
    }
};
