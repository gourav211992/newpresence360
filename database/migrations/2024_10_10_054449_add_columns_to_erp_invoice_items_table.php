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
        Schema::table('erp_invoice_items', function (Blueprint $table) {
            $table->unsignedBigInteger('dn_cum_invoice_id')->nullable()->after('sale_order_id')->comment('From which header this has been pulled');
            $table->unsignedBigInteger('invoice_id')->nullable()->after('dn_cum_invoice_id')->comment('From which header this has been pulled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_invoice_items', function (Blueprint $table) {
            //
        });
    }
};
