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
        Schema::table('erp_purchase_orders', function (Blueprint $table) {
            $table->enum('gate_entry_required',['yes','no'])->default('no')->after('group_currency_exg_rate');
            $table->enum('supp_invoice_required',['yes','no'])->default('no')->after('gate_entry_required');
        });
        Schema::table('erp_purchase_orders_history', function (Blueprint $table) {
            $table->enum('gate_entry_required',['yes','no'])->default('no')->after('group_currency_exg_rate');
            $table->enum('supp_invoice_required',['yes','no'])->default('no')->after('gate_entry_required');
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_purchase_orders_history', function (Blueprint $table) {
            $table->dropColumn('gate_entry_required');
            $table->dropColumn('supp_invoice_required');
        });
        Schema::table('erp_purchase_orders', function (Blueprint $table) {
            $table->dropColumn('gate_entry_required');
            $table->dropColumn('supp_invoice_required');
        });
    }
};
