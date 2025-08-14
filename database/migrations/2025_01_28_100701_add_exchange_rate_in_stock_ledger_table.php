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
            $table->decimal('org_currency_exg_rate', 15, 6)->after('org_currency_code')->nullable();
            $table->decimal('comp_currency_exg_rate', 15, 6)->after('comp_currency_code')->nullable();
            $table->decimal('group_currency_exg_rate', 15, 6)->after('group_currency_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_ledger', function (Blueprint $table) {
            $table->dropColumn('group_currency_exg_rate');
            $table->dropColumn('comp_currency_exg_rate');
            $table->dropColumn('org_currency_exg_rate');
        });
    }
};
