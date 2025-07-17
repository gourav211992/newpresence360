<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('erp_finance_fixed_asset_setup', function (Blueprint $table) {
            // allow NULL values
            $table->unsignedBigInteger('ledger_id')->nullable()->change();
            $table->unsignedBigInteger('ledger_group_id')->nullable()->change();
            $table->integer('expected_life_years')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('erp_finance_fixed_asset_setup', function (Blueprint $table) {
            // revert to NOT NULL
            $table->unsignedBigInteger('ledger_id')->nullable(false)->change();
            $table->unsignedBigInteger('ledger_group_id')->nullable(false)->change();
            $table->integer('expected_life_years')->nullable(false)->change();
        });
    }
};
