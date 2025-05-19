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
        Schema::table('erp_payment_vouchers', function (Blueprint $table) {
            $table->dropColumn('orgExchangeRate');
            $table->dropColumn('exchangeRateData');

            $table->integer('org_currency_id')->after('currencyCode')->nullable();
            $table->string('org_currency_code')->after('org_currency_id')->nullable();
            $table->string('org_currency_exg_rate')->after('org_currency_code')->nullable();

            $table->integer('comp_currency_id')->after('org_currency_exg_rate')->nullable();
            $table->string('comp_currency_code')->after('comp_currency_id')->nullable();
            $table->string('comp_currency_exg_rate')->after('comp_currency_code')->nullable();

            $table->integer('group_currency_id')->after('comp_currency_exg_rate')->nullable();
            $table->string('group_currency_code')->after('group_currency_id')->nullable();
            $table->string('group_currency_exg_rate')->after('group_currency_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_payment_vouchers', function (Blueprint $table) {
            $table->dropColumn('org_currency_id');
            $table->dropColumn('org_currency_code');
            $table->dropColumn('org_currency_exg_rate');

            $table->dropColumn('comp_currency_id');
            $table->dropColumn('comp_currency_code');
            $table->dropColumn('comp_currency_exg_rate');

            $table->dropColumn('group_currency_id');
            $table->dropColumn('group_currency_code');
            $table->dropColumn('group_currency_exg_rate');
        });
    }
};
