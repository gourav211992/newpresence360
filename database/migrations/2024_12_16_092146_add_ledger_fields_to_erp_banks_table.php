<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('erp_banks', function (Blueprint $table) {
            $table->unsignedBigInteger('ledger_id')->nullable()->after('group_id');
            $table->unsignedBigInteger('ledger_group_id')->nullable()->after('ledger_id');
            $table->foreign('ledger_id')->references('id')->on('erp_ledgers')->onDelete('set null');
            $table->foreign('ledger_group_id')->references('id')->on('erp_groups')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('erp_banks', function (Blueprint $table) {
            $table->dropForeign(['ledger_id']);
            $table->dropForeign(['ledger_group_id']);
            $table->dropColumn('ledger_id');
            $table->dropColumn('ledger_group_id');
        });
    }
};
