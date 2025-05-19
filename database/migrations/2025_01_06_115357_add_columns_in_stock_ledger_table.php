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
        Schema::table('stock_ledger', function (Blueprint $table) {
            $table->decimal('org_currency_cost_per_unit', 15,6)->default(0.00)->nullable()->after('org_currency_code');
            $table->decimal('comp_currency_cost_per_unit', 15,6)->default(0.00)->nullable()->after('comp_currency_code');
            $table->decimal('group_currency_cost_per_unit', 15,6)->default(0.00)->nullable()->after('group_currency_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_ledger', function (Blueprint $table) {
            $table->dropColumn('group_currency_cost_per_unit');
            $table->dropColumn('comp_currency_cost_per_unit');
            $table->dropColumn('org_currency_cost_per_unit');
        });
    }
};
