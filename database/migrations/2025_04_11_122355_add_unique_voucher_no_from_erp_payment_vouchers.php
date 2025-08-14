<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
       
        Schema::table('erp_payment_vouchers', function (Blueprint $table) {
            $table->unique('voucher_no');
        });
    }

    public function down(): void
    {
        Schema::table('erp_payment_vouchers', function (Blueprint $table) {
            $table->dropUnique('erp_payment_vouchers_voucher_no_unique');
        });
    }
};
