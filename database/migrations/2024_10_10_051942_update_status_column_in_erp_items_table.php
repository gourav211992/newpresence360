<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Helpers\ConstantHelper;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use raw SQL to modify the existing status column
        DB::statement("ALTER TABLE erp_items CHANGE status status ENUM('" . implode("','", ConstantHelper::USER_STATUS) . "') NOT NULL DEFAULT '" . ConstantHelper::DRAFT . "'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // If needed, you can revert back to the original enum values
        DB::statement("ALTER TABLE erp_items CHANGE status status ENUM('" . ConstantHelper::ACTIVE . "', '" . ConstantHelper::INACTIVE . "') NOT NULL DEFAULT '" . ConstantHelper::ACTIVE . "'");
    }
};
