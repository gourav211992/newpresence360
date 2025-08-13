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
        \DB::statement("ALTER TABLE erp_boms ALTER COLUMN bom_type SET DEFAULT 'fixed'");
        \DB::statement("ALTER TABLE erp_boms ALTER COLUMN customizable SET DEFAULT 'no'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \DB::statement("ALTER TABLE erp_boms ALTER COLUMN customizable DROP DEFAULT");
        \DB::statement("ALTER TABLE erp_boms ALTER COLUMN bom_type DROP DEFAULT");
    }
};
