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
        Schema::table('erp_finance_fixed_asset_sub', function (Blueprint $table) {
            $table->string('sales_date')->nullable();
            $table->string('status',50)->default('active')->after('sales_date');
            $table->bigInteger('sales_value')->nullable()->after('status');
            $table->bigInteger('profit_loss_value')->nullable()->after('sales_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_finance_fixed_asset_sub', function (Blueprint $table) {
            $table->dropColumn(['sales_date', 'status', 'sales_value', 'profit_loss_value']);
        });
    }
};
