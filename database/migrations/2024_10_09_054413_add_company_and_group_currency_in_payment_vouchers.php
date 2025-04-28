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
            $table->string('bookCode')->after('book_id');
            $table->string('bankCode')->after('bank_id');
            $table->string('accountNo')->after('account_id');
            $table->string('currencyCode')->after('currency_id');
            $table->renameColumn('exchange_rate','orgExchangeRate');
            $table->json('exchangeRateData')->nullable()->after('exchange_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_payment_vouchers', function (Blueprint $table) {
            $table->dropColumn('bookCode');
            $table->dropColumn('bankCode');
            $table->dropColumn('accountNo');

            if (Schema::hasColumn('erp_payment_vouchers','orgExchangeRate')) {
                $table->renameColumn('orgExchangeRate','exchange_rate');
            }

            if (Schema::hasColumn('erp_payment_vouchers','exchangeRateData')) {
                $table->dropColumn('exchangeRateData');
            }
        });
    }
};
