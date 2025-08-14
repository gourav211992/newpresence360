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
        Schema::table('erp_sale_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('org_currency_id')->nullable()->after('total_expense_value');
            $table->string('org_currency_code')->nullable()->after('org_currency_id');
            $table->decimal('org_currency_exg_rate', 15, 6)->nullable()->after('org_currency_code');
            $table->unsignedBigInteger('comp_currency_id')->nullable()->after('org_currency_exg_rate');
            $table->string('comp_currency_code')->nullable()->after('comp_currency_id');
            $table->decimal('comp_currency_exg_rate', 15, 6)->nullable()->after('comp_currency_code');
            $table->unsignedBigInteger('group_currency_id')->nullable()->after('comp_currency_exg_rate');
            $table->string('group_currency_code')->nullable()->after('group_currency_id');
            $table->decimal('group_currency_exg_rate', 15, 6)->nullable()->after('group_currency_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_sale_orders', function (Blueprint $table) {
            $table->dropColumn([
                'org_currency_id',
                'org_currency_code',
                'org_currency_exg_rate',
                'comp_currency_id',
                'comp_currency_code',
                'comp_currency_exg_rate',
                'group_currency_id',
                'group_currency_code',
                'group_currency_exg_rate'
            ]);
        });
    }
};
