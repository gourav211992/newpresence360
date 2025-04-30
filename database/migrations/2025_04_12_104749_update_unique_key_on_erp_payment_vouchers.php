<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('erp_payment_vouchers', function (Blueprint $table) {
            // Drop the existing unique index
            $table->dropUnique('erp_payment_vouchers_voucher_no_unique');

            // Add the new composite unique index
            $table->unique(['voucher_no', 'organization_id', 'group_id'], 'unique_voucher_org_group');
        });
    }

    public function down()
    {
        Schema::table('erp_payment_vouchers', function (Blueprint $table) {
            // Drop the composite index
            $table->dropUnique('unique_voucher_org_group');

            // Restore the original unique index on voucher_no
            $table->unique('voucher_no');
        });
    }
};
