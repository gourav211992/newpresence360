<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('erp_payment_vouchers', function (Blueprint $table) {
            $table->unsignedBigInteger('ledger_group_id')->after('ledger_id')->nullable()->comment('Reference to ledger group');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('erp_payment_vouchers', function (Blueprint $table) {
            $table->dropColumn('ledger_group_id');
        });
    }

};
