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
            $table->enum('partial_delivery', ['yes','no'])->default('no')->after('supp_invoice_required');
        });
        Schema::table('erp_purchase_orders_history', function (Blueprint $table) {
            $table->enum('partial_delivery', ['yes','no'])->default('no')->after('supp_invoice_required');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_purchase_orders_history', function (Blueprint $table) {
            $table->dropColumn('partial_delivery');
        });
        Schema::table('erp_purchase_orders', function (Blueprint $table) {
            $table->dropColumn('partial_delivery');
        });
    }
};
