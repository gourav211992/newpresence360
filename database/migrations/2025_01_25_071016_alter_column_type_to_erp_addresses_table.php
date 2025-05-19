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
        Schema::table('erp_addresses', function (Blueprint $table) {
            # type location added for store master address
            DB::statement("ALTER TABLE `erp_addresses` MODIFY COLUMN `type` ENUM('shipping', 'billing', 'both', 'location') NULL");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_addresses', function (Blueprint $table) {
            DB::statement("ALTER TABLE `erp_addresses` MODIFY COLUMN `type` ENUM('shipping', 'billing', 'both') NULL");
        });
    }
};
