<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE erp_tax_details CHANGE tax_type tax_type VARCHAR(255) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE erp_tax_details CHANGE tax_type tax_type VARCHAR(255) NULL');
    }
};
