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
        Schema::table('erp_discount_master', function (Blueprint $table) {
            $table->unsignedBigInteger('discount_ledger_group_id')->nullable()->after('discount_ledger_id');
            $table->foreign('discount_ledger_group_id')->references('id')->on('erp_groups')->onDelete('set null');
        });
        Schema::table('erp_expense_master', function (Blueprint $table) {
            $table->unsignedBigInteger('expense_ledger_group_id')->nullable()->after('expense_ledger_id');
            $table->foreign('expense_ledger_group_id')->references('id')->on('erp_groups')->onDelete('set null');
            $table->unsignedBigInteger('service_provider_ledger_group_id')->nullable()->after('expense_ledger_group_id');
            $table->foreign('service_provider_ledger_group_id')->references('id')->on('erp_groups')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_discount_master', function (Blueprint $table) {
            $table->dropForeign(['ledger_group_id']);
            $table->dropColumn('ledger_group_id');
        });
        Schema::table('erp_expense_master', function (Blueprint $table) {
            $table->dropForeign(['ledger_group_id', 'service_provider_ledger_group_id']);
            $table->dropColumns(['ledger_group_id', 'service_provider_ledger_group_id']);
        });
    }
};
