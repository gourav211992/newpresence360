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
        Schema::table('erp_sale_orders', function (Blueprint $table) {
            $table->decimal('total_amount', 15, 2)->default(0.00)->after('total_expense_value');
        });
        Schema::table('erp_so_items', function (Blueprint $table) {
            $table->decimal('total_item_amount', 15, 2)->default(0.00)->after('header_expense_amount');
        });

        Schema::table('erp_sale_invoices', function (Blueprint $table) {
            $table->decimal('total_amount', 15, 2)->default(0.00)->after('total_expense_value');
        });
        Schema::table('erp_invoice_items', function (Blueprint $table) {
            $table->decimal('total_item_amount', 15, 2)->default(0.00)->after('header_expense_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_sale_orders', function (Blueprint $table) {
            $table->dropColumn('total_amount');
        });
    
        Schema::table('erp_so_items', function (Blueprint $table) {
            $table->dropColumn('total_item_amount');
        });
    
        Schema::table('erp_sale_invoices', function (Blueprint $table) {
            $table->dropColumn('total_amount');
        });
    
        Schema::table('erp_invoice_items', function (Blueprint $table) {
            $table->dropColumn('total_item_amount');
        });
    }
};
