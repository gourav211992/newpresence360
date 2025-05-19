<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('erp_vouchers', function (Blueprint $table) {
            DB::statement('ALTER TABLE `erp_vouchers` CHANGE `org_exchange_rate` `org_currency_exg_rate` DECIMAL(15, 6) NULL AFTER `currency_id`');
            $table->string('currency_code')->nullable()->after('currency_id');
            $table->unsignedBigInteger('org_currency_id')->nullable()->after('currency_code');
            $table->string('org_currency_code')->nullable()->after('org_currency_id');
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
     *
     * @return void
     */
    public function down()
    {
        Schema::table('erp_vouchers', function (Blueprint $table) {
            $table->dropColumn([
                'currency_code',
                'org_currency_id',
                'org_currency_code',
                'org_currency_exg_rate',
                'comp_currency_id',
                'comp_currency_code',
                'comp_currency_exg_rate',
                'group_currency_id',
                'group_currency_code',
                'group_currency_exg_rate',
            ]);
        });
    }
};
