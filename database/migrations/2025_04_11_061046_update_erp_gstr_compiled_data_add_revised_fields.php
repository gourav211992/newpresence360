<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        
        DB::statement("ALTER TABLE erp_gstr_compiled_data CHANGE `Revised_invoice_no` `revised_invoice_no` VARCHAR(50) NULL");
        DB::statement("ALTER TABLE erp_gstr_compiled_data CHANGE `Revised_invoice_date` `revised_invoice_date` DATE NULL");
        DB::statement("ALTER TABLE erp_gstr_compiled_data MODIFY `year` VARCHAR(255) NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            UPDATE erp_gstr_compiled_data
            SET year = NULL
            WHERE year REGEXP '[^0-9]'
        ");
        
        DB::statement("ALTER TABLE erp_gstr_compiled_data CHANGE `revised_invoice_no` `Revised_invoice_no` VARCHAR(50) NULL");
        DB::statement("ALTER TABLE erp_gstr_compiled_data CHANGE `revised_invoice_date` `Revised_invoice_date` DATE NULL");
        DB::statement("ALTER TABLE erp_gstr_compiled_data MODIFY `year` INT NULL");
    }
};
