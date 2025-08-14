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
        Schema::table('erp_bank_details', function (Blueprint $table) {
            $table->unsignedBigInteger('ledger_id')->nullable()->after('bank_id');
            $table->unsignedBigInteger('ledger_group_id')->nullable()->after('ledger_id');
            $table->foreign('ledger_id')->references('id')->on('erp_ledgers')->onDelete('set null');
            $table->foreign('ledger_group_id')->references('id')->on('erp_groups')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_bank_details', function (Blueprint $table) {
            $table->dropForeign(['ledger_id']);
            $table->dropForeign(['ledger_group_id']);
            $table->dropColumn('ledger_id');
            $table->dropColumn('ledger_group_id');
        });
    }
};
