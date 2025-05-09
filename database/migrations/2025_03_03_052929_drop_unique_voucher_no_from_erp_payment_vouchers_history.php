<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('erp_payment_vouchers_history', function (Blueprint $table) {
            $table->dropUnique('erp_payment_vouchers_history_voucher_no_unique');
        });
    }

    public function down()
    {
        Schema::table('erp_payment_vouchers_history', function (Blueprint $table) {
            $table->unique('voucher_no', 'erp_payment_vouchers_history_voucher_no_unique');
        });
    }
};
