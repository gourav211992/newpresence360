<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('erp_finance_fixed_asset_setup', function (Blueprint $table) {
            $table->decimal('salvage_percentage', 8, 2)->nullable()->after('expected_life_years'); // Adjust 'after' as needed
        });
    }

    public function down(): void
    {
        Schema::table('erp_finance_fixed_asset_setup', function (Blueprint $table) {
            $table->dropColumn('salvage_percentage');
        });
    }
};
