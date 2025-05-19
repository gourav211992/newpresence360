<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('erp_payment_vouchers', function (Blueprint $table) {
            $table->integer('approval_level')->default(1)->change();
        });
    }

    public function down()
    {
        Schema::table('erp_payment_vouchers', function (Blueprint $table) {
            $table->integer('approval_level')->default(null)->change();
        });
    }
};
